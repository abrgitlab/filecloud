<?php
/**
 * Created by PHPStorm.
 * User: daemon
 * Date: 06.05.16
 * Time: 23:26
 */

namespace app\models;


use app\components\Chunks;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Url;
use yii\web\ServerErrorHttpException;
use yii\web\UploadedFile;

/**
 * This is the model class for table "files".
 *
 * @property integer $id
 * @property string $title
 * @property string $shortlink
 * @property int $size
 * @property string $chunks
 * @property integer $loading_state
 * @property integer $uploaded_at
 * @property integer $user_id
 */

//TODO: разобратся с uploaded_at

class Files extends ActiveRecord
{

    /**
     * Статус загрузки "в процессе"
     */
    const LOADING_STATE_IN_PROCESS = 0;

    /**
     * Статус загрузки "загружен"
     */
    const LOADING_STATE_LOADED = 1;

    /**
     * @var string $upload_directory
     */
    public $upload_directory;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'files';
    }

    function init()
    {
        parent::init();

        $this->upload_directory = Yii::getAlias('@webroot/media') . DIRECTORY_SEPARATOR;
        if (!is_dir($this->upload_directory)) {
            mkdir($this->upload_directory);
        }
    }

    /**
     * Генерирует короткую ссылку на файл
     *
     * @param int|string $id
     * @return string
     */
    private function generateLink($id) {
        $result = '';

        $length = 3 * (floor(strlen($id) / 3) + 1);

        $id_p = 0;
        for ($i = 0; $i < $length; ++$i) {
            if (strlen($id) - $id_p >= $length - $i) {
                $result .= chr(substr($id, $id_p, 1));
                ++$id_p;
            }
            else {
                $chance = rand(11, 12);
                if ($chance == 11 || $id_p == strlen($id)) {
                    $rand = rand(10, 255);
                    $result .= chr($rand);
                } else {
                    $result .= chr(substr($id, $id_p, 1));
                    ++$id_p;
                }
            }
        }

        return base64_encode($result);
    }

    /**
     * @return string
     * @throws \Exception
     */
    public static function uploadFile() {
        $tmp_file = UploadedFile::getInstanceByName('FileLoader[file]');

        $content_range = Yii::$app->request->headers->get('content-range');
        preg_match('/attachment; filename="([^"]+)"/', Yii::$app->request->headers->get('content-disposition'), $content_disposition_result);

        if ($tmp_file) {
            $response = [];

            if ($content_range) {
                $content_range = preg_split('/[^0-9]+/', $content_range);
                $range_start = $content_range[1];
                $range_end = $content_range[2];
                $full_size = $content_range[3];

                $file_model = Files::findOne(['title' => $tmp_file->name, 'loading_state' => Files::LOADING_STATE_IN_PROCESS, 'size' => $full_size]);

                if (!$file_model) {
                    $file_model = new Files();
                    $file_model->title = $tmp_file->name;
                    $file_model->loading_state = Files::LOADING_STATE_IN_PROCESS;
                    $file_model->user_id = Yii::$app->user->getId();
                    $file_model->size = $full_size;
                    $file_model->loadDefaultValues(true)->save();

                    do {
                        $file_model->shortlink = $file_model->generateLink($file_model->id);
                    } while (strpos($file_model->shortlink, '/') || strpos($file_model->shortlink, '+'));
                    $file_model->save();
                }

                $file_path = $file_model->upload_directory . $file_model->shortlink;
                $file_exists = file_exists($file_path);
                $file = fopen($file_path, 'c');

                if ($file) {
                    if (!$file_exists)
                        fwrite($file, str_repeat("\0", $full_size));
                    fseek($file, $range_start);
                    fwrite($file, file_get_contents($tmp_file->tempName), $range_end - $range_start + 1);
                    fclose($file);

                    $chunks = json_decode($file_model->chunks, true);

                    $result_chunks = Chunks::addChunk($chunks, ['begin' => (int)$range_start, 'end' => (int)$range_end]);
                    $file_model->chunks = json_encode($result_chunks);
                    if (count($result_chunks) == 1 && $result_chunks[0]['begin'] == 0 && $result_chunks[0]['end'] == $full_size - 1) {
                        $file_model->loading_state = Files::LOADING_STATE_LOADED;
                        $response = [
                            'files' => [[
                                'name' => $file_model->title,
                                'size' => $full_size,
                                'url' => Url::to(['site/get', 'shortlink' => $file_model->shortlink]),
                            ]]
                        ];
                    }
                    $file_model->save();
                } else {
                    throw new ServerErrorHttpException('I can\'t create new file');
                }
            } else {
                $file_model = new Files();
                $file_model->title = $tmp_file->name;
                $file_model->loading_state = Files::LOADING_STATE_LOADED;
                $file_model->user_id = Yii::$app->user->getId();
                $file_model->size = $tmp_file->size;
                $file_model->chunks = json_encode([['begin' => 0, 'end' => $tmp_file->size]]);
                $file_model->loadDefaultValues(true)->save();

                do {
                    $file_model->shortlink = $file_model->generateLink($file_model->id);
                } while (strpos($file_model->shortlink, '/') || strpos($file_model->shortlink, '+'));
                $file_model->save();

                $file_path = $file_model->upload_directory . $file_model->shortlink;
                if ($tmp_file->saveAs($file_path)) {
                    $response = [
                        'files' => [[
                            'name' => $file_model->title,
                            'size' => $tmp_file->size,
                            'url' => Url::to(['site/get', 'shortlink' => $file_model->shortlink]),
                        ]]
                    ];
                } else {
                    throw new ServerErrorHttpException('I can\'t create new file');
                }
            }

            return $response;
        }

        throw new ServerErrorHttpException('Error while uploading file');

        /*$tmp_file = UploadedFile::getInstanceByName('FileLoader[file]');
        $content_range = Yii::$app->request->headers->get('content-range');
        preg_match('/attachment; filename="([^"]+)"/', Yii::$app->request->headers->get('content-disposition'), $content_disposition_result);
        $content_disposition = NULL;
        if (isset($content_disposition_result[1]))
            $content_disposition = $content_disposition_result[1];

        if ($tmp_file) {
            $response = [];

            if ($content_range) {
                $content_range = preg_split('/[^0-9]+/', $content_range);
                $range_start = $content_range[1];
                $range_end = $content_range[2];
                $full_size = $content_range[3];

                if ($range_start == 0) {
                    $file_model = new Files();
                    $file_model->title = $tmp_file->name;
                    $file_model->loading_state = Files::LOADING_STATE_IN_PROCESS;
                    $file_model->user_id = Yii::$app->user->getId();
                    $file_model->save();

                    do {
                        $file_model->shortlink = $file_model->generateLink($file_model->id);
                    } while (strpos($file_model->shortlink, '/') || strpos($file_model->shortlink, '+'));
                    $file_model->save();

                    if (isset(Yii::$app->session->get('files')[$content_disposition])) {
                        $new_files = Yii::$app->session->get('files');
                        unset($new_files[$content_disposition]);
                        Yii::$app->session->set('files', $new_files);
                    }
                    Yii::$app->session->set('files', ArrayHelper::merge(Yii::$app->session->get('files'), [$content_disposition => $file_model->shortlink]));

                    $file_path = $file_model->upload_directory . $file_model->shortlink;
                    if (!$tmp_file->saveAs($file_path)) {
                        throw new ServerErrorHttpException('I can\'t create new file');
                    }
                } elseif ($content_disposition && isset(Yii::$app->session->get('files')[$content_disposition]) && $shortlink = Yii::$app->session->get('files')[$content_disposition]) {
                    $file_model = Files::findOne(['shortlink' => $shortlink, 'loading_state' => Files::LOADING_STATE_IN_PROCESS]);
                    if ($file_model) {
                        if ($range_end + 1 == $full_size) {
                            $new_files = Yii::$app->session->get('files');
                            unset($new_files[$content_disposition]);
                            Yii::$app->session->set('files', $new_files);
                            $file_model->loading_state = Files::LOADING_STATE_LOADED;
                            $file_model->save();

                            $response = [
                                'files' => [[
                                    'name' => $file_model->title,
                                    'size' => $full_size,
                                    'url' => Url::to(['site/get', 'shortlink' => $file_model->shortlink]),
                                ]]
                            ];
                        } elseif ($range_end + 1 > $full_size) {
                            throw new ServerErrorHttpException('Chunk range ends after full file size');
                        }

                        $file_path = $file_model->upload_directory . $file_model->shortlink;
                        file_put_contents(
                            $file_path,
                            fopen($tmp_file->tempName, 'r'),
                            FILE_APPEND
                        );
                        $tmp_file->reset();
                    } else {
                        throw new ServerErrorHttpException('Loading file not found');
                    }
                } else {
                    throw new ServerErrorHttpException('I can\'t tie this chunk to any file');
                }
            } else {
                $file_model = new Files();
                $file_model->title = $tmp_file->name;
                $file_model->loading_state = Files::LOADING_STATE_LOADED;
                $file_model->user_id = Yii::$app->user->getId();
                $file_model->save();

                do {
                    $file_model->shortlink = $file_model->generateLink($file_model->id);
                } while (strpos($file_model->shortlink, '/') || strpos($file_model->shortlink, '+'));
                $file_model->save();

                $file_path = $file_model->upload_directory . $file_model->shortlink;
                if ($tmp_file->saveAs($file_path)) {
                    $response = [
                        'files' => [[
                            'name' => $file_model->title,
                            'size' => $tmp_file->size,
                            'url' => Url::to(['site/get', 'shortlink' => $file_model->shortlink]),
                        ]]
                    ];
                } else {
                    throw new ServerErrorHttpException('I can\'t create new file');
                }
            }

            return $response;
        }

        throw new ServerErrorHttpException('Error while uploading file');*/
    }

    public function delete() {
        unlink($this->upload_directory . $this->shortlink);
        return parent::delete();
    }

}