<?php

/**
 * Created by PHPStorm.
 * User: daemon
 * Date: 21.10.16
 * Time: 0:31
 */

namespace app\components;

use yii\base\Object;

class Chunks extends Object {

    /**
     * Производит наложение chunk1 на chunk2
     *
     * @param $chunk1
     * @param $chunk2
     * @param $result
     * @return bool|null
     */
    public static function mergeChunks($chunk1, $chunk2, &$result) {
        if ($chunk1['end'] >= $chunk2['begin'] - 1 && $chunk2['end'] >= $chunk1['begin'] - 1) {
            $result = [['begin' => min($chunk1['begin'], $chunk2['begin']), 'end' => max($chunk1['end'], $chunk2['end'])]];
            return null;
        } elseif ($chunk1['begin'] < $chunk2['begin']) {
            $result = [$chunk1, $chunk2];
            return false;
        } else {
            $result = [$chunk2, $chunk1];
            return true;
        }
    }

    /**
     * Добавляет в коллецию chunks новый chunk
     *
     * @param $chunks
     * @param $newChunk
     * @return array
     */
    public static function addChunk($chunks, $newChunk) {
        $newChunks = [];

        if (count($chunks) == 0)
            $newChunks[] = $newChunk;

        $new_chunk_added = false;
        for ($i = 0; $i < count($chunks); ++$i) {
            if ($new_chunk_added) {
                $merged1 = [$chunks[$i]];
                $swapped = false;
            } else {
                $swapped = static::mergeChunks($chunks[$i], $newChunk, $merged1);
            }

            if (count($merged1) == 1) {
                $new_chunk_added = true;
                if (count($newChunks) > 0) {
                    static::mergeChunks($newChunks[count($newChunks) - 1], $merged1[0], $merged2);
                    if (count($merged2) == 1) {
                        $newChunks[count($newChunks) - 1] = $merged2[0];
                    } else {
                        $newChunks[] = $merged1[0];
                    }
                } else {
                    $newChunks[] = $merged1[0];
                }
            } else {
                $newChunks[] = $merged1[0];
                if ($swapped) {
                    $new_chunk_added = true;
                    $newChunks[] = $merged1[1];
                }
            }
        }

        return $newChunks;
    }

}