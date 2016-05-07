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
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\UploadedFile;

/**
 * This is the model class for table "files".
 *
 * @property integer $id
 * @property string $title
 * @property string $shortlink
 * @property integer $uploaded_at
 */
class Files extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'files';
    }

//    private function generateLink($id) {
//        $result = '';
//
//        $steps = 3 - ((strlen($id) - 1) % 3 + 1);
//        for ($i = 0; $i < $steps; ++$i) {
//            while (($rand = rand(0, 255)) >= 48 && $rand <= 57 ); //Избавляемся от ASCII-диапазона 30-39 (цифры), дабы избежать дубляжа коротких ссылок
//            $result .= chr($rand);
//        }
//
//        for ($i = 0; $i < strlen($id); ++$i) {
//            $result .= chr(substr($id, $i, 1));
//        }
//
//        return base64_encode($result);
//    }

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
    public function uploadFile() {
        $file = UploadedFile::getInstanceByName('FileLoader[file]');
        $directory = Yii::getAlias('@webroot/media') . DIRECTORY_SEPARATOR;
        if (!is_dir($directory)) {
            mkdir($directory);
        }

        if ($file) {
            $this->title = $file->name;
            $this->uploaded_at = time();
            $this->save();

            do {
                $this->shortlink = $this->generateLink($this->id);
            } while (strpos($this->shortlink, '/') || strpos($this->shortlink, '+'));
            $this->save();

            $filePath = $directory . $this->shortlink;
            if ($file->saveAs($filePath)) {
                $path = Url::to(['site/get', 'shortlink' => $this->shortlink]);
                return Json::encode([
                    'files' => [[
                        'name' => $this->title,
                        'size' => $file->size,
                        'url' => $path,
                    ]]
                ]);
            } else
                $this->delete();
        }
        return Json::encode([]);
    }

}