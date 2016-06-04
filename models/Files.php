<?php
/**
 * Created by PHPStorm.
 * User: daemon
 * Date: 06.05.16
 * Time: 23:26
 */

namespace app\models;


use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\ServerErrorHttpException;
use yii\web\UploadedFile;

/**
 * This is the model class for table "files".
 *
 * @property integer $id
 * @property string $title
 * @property string $shortlink
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

                    $filePath = $file_model->upload_directory . $file_model->shortlink;
                    if (!$tmp_file->saveAs($filePath)) {
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

                        $filePath = $file_model->upload_directory . $file_model->shortlink;
                        file_put_contents(
                            $filePath,
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

                $filePath = $file_model->upload_directory . $file_model->shortlink;
                if ($tmp_file->saveAs($filePath)) {
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

            return Json::encode($response);
        }

        throw new ServerErrorHttpException('Error while uploading file');
    }

}