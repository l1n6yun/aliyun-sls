<?php

declare(strict_types=1);
/**
 * This file is part of l1n6yun/aliyun-sls.
 *
 * @link     https://github.com/l1n6yun/aliyun-sls
 * @contact  l1n6yun@gmail.com
 * @license  https://github.com/l1n6yun/aliyun-sls/blob/master/LICENSE
 */

namespace L1n6yun\AliyunSls;

use Exception;

class Log
{
    private $_unknown;

    // required uint32 time = 1;

    private $time_;

    // repeated .Log.Content contents = 2;

    private $contents_;

    public function __construct($in = null, &$limit = PHP_INT_MAX)
    {
        if ($in !== null) {
            if (is_string($in)) {
                $fp = fopen('php://memory', 'r+b');
                fwrite($fp, $in);
                rewind($fp);
            } elseif (is_resource($in)) {
                $fp = $in;
            } else {
                throw new Exception('Invalid in parameter');
            }
            $this->read($fp, $limit);
        }
    }

    public function __toString()
    {
        return ''
            . Protobuf::toString('unknown', $this->_unknown)
            . Protobuf::toString('time_', $this->time_)
            . Protobuf::toString('contents_', $this->contents_);
    }

    public function read($fp, &$limit = PHP_INT_MAX)
    {
        while (! feof($fp) && $limit > 0) {
            $tag = Protobuf::read_varint($fp, $limit);
            if ($tag === false) {
                break;
            }
            $wire = $tag & 0x07;
            $field = $tag >> 3;
            // var_dump("Log: Found $field type " . Protobuf::get_wiretype($wire) . " $limit bytes left");
            switch ($field) {
                case 1:
                    assert('$wire == 0');
                    $tmp = Protobuf::read_varint($fp, $limit);
                    if ($tmp === false) {
                        throw new Exception('Protobuf::read_varint returned false');
                    }
                    $this->time_ = $tmp;

                    break;
                case 2:
                    assert('$wire == 2');
                    $len = Protobuf::read_varint($fp, $limit);
                    if ($len === false) {
                        throw new Exception('Protobuf::read_varint returned false');
                    }
                    $limit -= $len;
                    $this->contents_[] = new LogContent($fp, $len);
                    assert('$len == 0');
                    break;
                default:
                    $this->_unknown[$field . '-' . Protobuf::get_wiretype($wire)][] = Protobuf::read_field($fp, $wire, $limit);
            }
        }
        if (! $this->validateRequired()) {
            throw new Exception('Required fields are missing');
        }
    }

    public function write($fp)
    {
        if (! $this->validateRequired()) {
            throw new Exception('Required fields are missing');
        }
        if (! is_null($this->time_)) {
            fwrite($fp, "\x08");
            Protobuf::write_varint($fp, $this->time_);
        }
        if (! is_null($this->contents_)) {
            foreach ($this->contents_ as $v) {
                fwrite($fp, "\x12");
                Protobuf::write_varint($fp, $v->size()); // message
                $v->write($fp);
            }
        }
    }

    public function size()
    {
        $size = 0;
        if (! is_null($this->time_)) {
            $size += 1 + Protobuf::size_varint($this->time_);
        }
        if (! is_null($this->contents_)) {
            foreach ($this->contents_ as $v) {
                $l = $v->size();
                $size += 1 + Protobuf::size_varint($l) + $l;
            }
        }
        return $size;
    }

    public function validateRequired()
    {
        if ($this->time_ === null) {
            return false;
        }
        return true;
    }

    public function clearTime()
    {
        $this->time_ = null;
    }

    public function hasTime()
    {
        return $this->time_ !== null;
    }

    public function getTime()
    {
        if ($this->time_ === null) {
            return 0;
        }  return $this->time_;
    }

    public function setTime($value)
    {
        $this->time_ = $value;
    }

    public function clearContents()
    {
        $this->contents_ = null;
    }

    public function getContentsCount()
    {
        if ($this->contents_ === null) {
            return 0;
        }  return count($this->contents_);
    }

    public function getContents($index)
    {
        return $this->contents_[$index];
    }

    public function getContentsArray()
    {
        if ($this->contents_ === null) {
            return [];
        }  return $this->contents_;
    }

    public function setContents($index, $value)
    {
        $this->contents_[$index] = $value;
    }

    public function addContents($value)
    {
        $this->contents_[] = $value;
    }

    public function addAllContents(array $values)
    {
        foreach ($values as $value) {
            $this->contents_[] = $value;
        }
    }

    // @@protoc_insertion_point(class_scope:Log)
}
