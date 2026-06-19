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
        $content = file_get_contents($file);
        if ($content === false || $content === '') {
            self::$cache[$collection] = array();
            return array();
        }
        $data = json_decode($content, true);
        self::$cache[$collection] = is_array($data) ? $data : array();
        return self::$cache[$collection];
    }

    public static function write($collection, $data) {
        self::$cache[$collection] = $data;
        $file = self::getFilePath($collection);
        $dir = dirname($file);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        return file_put_contents($file, $json, LOCK_EX) !== false;
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
                $item = $data;
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
        $maxId = 0;
        foreach ($items as $item) {
            if (isset($item['id']) && $item['id'] > $maxId) {
                $maxId = $item['id'];
            }
        }
        return $maxId + 1;
    }
}
