<?php
namespace Hirak\PHPP;

class PHPP
{
     public $context;

     private $fp;

     private $source = '';
     private $position = 0;
     private $length = 0;

     public function do_origin(callable $fn)
     {
         stream_wrapper_restore('file');
         $ret = $fn();
         stream_wrapper_unregister('file');
         stream_wrapper_register('file', __CLASS__);
         return $ret;
     }

     public function dir_closedir()
     {
         closedir($this->fp);
     }

     public function dir_opendir($path, $options)
     {
         return $this->do_origin(function() use($path) {
             $this->fp = opendir($path, $this->context);
         });
     }

     public function dir_readdir()
     {
         return readdir($this->fp);
     }

     public function dir_rewinddir()
     {
         return rewinddir($this->fp);
     }

     public function mkdir($path, $mode, $options)
     {
         return $this->do_origin(function () use($path, $mode) {
             return mkdir($path, $mode, 0, $this->context);
         });
     }

     public function rename($path_from, $path_to)
     {
         return $this->do_origin(function() use($path_from, $path_to) {
             return rename($path_from, $path_to, $this->context);
         });
     }

     public function rmdir($path, $options)
     {
         return $this->do_origin(function() use($path) {
             return rmdir($path, $this->context);
         });
     }

     public function stream_cast($cast_as)
     {
         return $this->fp;
     }

     public function stream_close()
     {
         return fclose($this->fp);
     }

     public function stream_eof()
     {
         return feof($this->fp);
     }

     public function stream_flush()
     {
         return fflush($this->fp);
     }

     public function stream_lock($operation)
     {
         return flock($this->fp, $operation);
     }

     public function stream_metadata($path, $option, $value)
     {
         return $this->do_origin(function() use($path, $option, $value) {
             switch ($option) {
                 case STREAM_META_TOUCH:
                     return touch($path, $value[0], $value[1]);
                 case STREAM_META_OWNER_NAME:
                 case STREAM_META_OWNER:
                     return chown($path, $value);
                 case STREAM_META_GROUP_NAME:
                 case STREAM_META_GROUP:
                     return chgrp($path, $value);
                 case STREAM_META_ACCESS:
                     return chmod($path, $value);
             }
         });
     }

     public function stream_open($path, $mode, $options, &$opened_path)
     {
         return $this->do_origin(function() use ($path, $mode) {
             if (preg_match('/\.php\.php$/', $path)) {
                 ob_start();
                 require $path;
                 $out = ob_get_clean();
                 if (substr($out, 0, 5) === '<' . '?php') {
                     $this->source = $out;
                     $this->length = strlen($out);
                 }
             }

             if ($this->context) {
                 return $this->fp = fopen($path, $mode, false, $this->context);
             } else {
                 return $this->fp = fopen($path, $mode, false);
             }
         });
     }

     public function stream_read($count)
     {
         if ($this->source) {
             if ($this->position > $this->length) {
                 return false;
             }
             $substr = substr($this->source, $this->position, $count);
             $this->position += $count;
             return $substr;
         }

         return fread($this->fp, $count);
     }

     public function stream_seek($offset, $whence = SEEK_SET)
     {
         if ($this->source) {
             $this->position = $offset;
         } else {
             return fseek($this->fp, $offset, $whence);
         }
     }

     /*
     public function stream_set_option($option, $arg1, $arg2)
     {
     }
    */

     public function stream_stat()
     {
         return fstat($this->fp);
     }

     public function stream_tell()
     {
         if ($this->source) {
             return $this->position;
         }
         return ftell($this->fp);
     }

     public function stream_truncate($new_size)
     {
         return ftruncate($this->fp, $new_size);
     }

     public function stream_write($data)
     {
         return fwrite($this->fp, $data);
     }

     public function unlink($path)
     {
         return $this->do_origin(function() use($path) {
             return unlink($path);
         });
     }

     public function url_stat($path, $flags)
     {
         return $this->do_origin(function() use($path, $flags) {
             return fstat($path, $flags);
         });
     }
}

stream_wrapper_unregister('file');
stream_wrapper_register('file', 'Hirak\PHPP\PHPP');
