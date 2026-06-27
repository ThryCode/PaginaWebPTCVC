<?php
/**
 * Almacenamiento en archivos JSON
 * Reemplaza la base de datos
 */

require_once 'config.php';

class Storage {

    private static $cache = array();

    public static function getFilePath($collection) {
        $collection = preg_replace('/[^a-zA-Z0-9_\-]/', '', $collection);
        return DATA_DIR . '/' . $collection . '.json';
    }

    public static function clearCache($collection = null) {
        if ($collection === null) {
            self::$cache = array();
        } else {
            unset(self::$cache[$collection]);
        }
    }

    public static function read($collection) {
        if (array_key_exists($collection, self::$cache)) {
            return self::$cache[$collection];
        }
        $file = self::getFilePath($collection);
        if (!file_exists($file)) {
            self::$cache[$collection] = array();
            return array();
        }
        $fh = fopen($file, 'r');
        if (!$fh) {
            self::$cache[$collection] = array();
            return array();
        }
        flock($fh, LOCK_SH);
        $content = stream_get_contents($fh);
        flock($fh, LOCK_UN);
        fclose($fh);
        if ($content === false || $content === '') {
            self::$cache[$collection] = array();
            return array();
        }
        $data = json_decode($content, true);
        if (!is_array($data)) {
            error_log('[Storage] Invalid JSON in ' . $collection . ': ' . json_last_error_msg());
        }
        self::$cache[$collection] = is_array($data) ? $data : array();
        return self::$cache[$collection];
    }

    public static function write($collection, $data) {
        $file = self::getFilePath($collection);
        $dir = dirname($file);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            error_log('[Storage] JSON encode failed for ' . $collection . ': ' . json_last_error_msg());
            return false;
        }
        $tmp = $file . '.tmp';
        $written = file_put_contents($tmp, $json, LOCK_EX);
        if ($written !== false) {
            rename($tmp, $file);
            self::$cache[$collection] = $data;
            return true;
        }
        @unlink($tmp);
        return false;
    }

    public static function findById($collection, $id) {
        $items = self::read($collection);
        foreach ($items as $item) {
            if (isset($item['id']) && $item['id'] == $id) {
                return $item;
            }
        }
        return null;
    }

    public static function findWhere($collection, $conditions) {
        $items = self::read($collection);
        $results = array();
        foreach ($items as $item) {
            $match = true;
            foreach ($conditions as $key => $value) {
                if (!isset($item[$key]) || $item[$key] != $value) {
                    $match = false;
                    break;
                }
            }
            if ($match) {
                $results[] = $item;
            }
        }
        return $results;
    }

    public static function insert($collection, $item) {
        $items = self::read($collection);
        $item['id'] = self::nextId($items);
        $item['created_at'] = date('Y-m-d H:i');
        $items[] = $item;
        self::write($collection, $items);
        return $item;
    }

    public static function update($collection, $id, $data) {
        $items = self::read($collection);
        foreach ($items as &$item) {
            if (isset($item['id']) && $item['id'] == $id) {
                $data['id'] = $item['id'];
                $data['created_at'] = $item['created_at'];
                $data['updated_at'] = date('Y-m-d H:i');
                $item = array_merge($item, $data);
                self::write($collection, $items);
                return $item;
            }
        }
        return null;
    }

    public static function delete($collection, $id) {
        $items = self::read($collection);
        $newItems = array();
        $found = false;
        foreach ($items as $item) {
            if (isset($item['id']) && $item['id'] == $id) {
                $found = true;
                continue;
            }
            $newItems[] = $item;
        }
        if ($found) {
            self::write($collection, $newItems);
        }
        return $found;
    }

    public static function count($collection, $conditions = array()) {
        $items = self::read($collection);
        if (empty($conditions)) {
            return count($items);
        }
        $count = 0;
        foreach ($items as $item) {
            $match = true;
            foreach ($conditions as $key => $value) {
                if (!isset($item[$key]) || $item[$key] != $value) {
                    $match = false;
                    break;
                }
            }
            if ($match) $count++;
        }
        return $count;
    }

    private static function nextId($items) {
        $ids = array_map(function($item) {
            return isset($item['id']) ? $item['id'] : 0;
        }, $items);
        return empty($ids) ? 1 : max($ids) + 1;
    }
}
