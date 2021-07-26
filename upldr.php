<?php
if (!defined("UPLDR")) {
    die("'UPLDR` constant is not declared. Do not use directly.");
}


class Upldr
{
    private $BUCKET = "";
    private $PWD = "/";
    private $media_store;
    private $writable = false;

    public function __construct($bucket, $writable = false) {
        include __DIR__."/upldr.config.php";
        if ($bucket !== preg_replace("/[^a-zA-Z0-9\-_]+/", "", $bucket)) {
            throw new Exception("[upldr.php] Bucket name illegal! Only a-zA-Z0-9 and -,_ is allowed");
        }
        $this->BUCKET = $bucket;
        $this->media_store = $upldr_media_store;
        $this->writable = $writable;
        if (!file_exists($this->join_paths())) {
            $this->pmkdir($this->join_paths());
        }
    }
    
    public function pwd() {
        return $this->PWD;
    }

    public function cd($path) {
        if (substr($path, 0, 1) == "/") {
            // This will fail in case of path traversal attacak (I hope)
            $this->localpath("/".$this->media_store."/".$this->BUCKET."/".$path);
            $this->PWD = $this->localpath($this->media_store."/".$this->BUCKET."/".$path);
        } else {
            $this->localpath($this->join_paths($path));
            $this->PWD = $this->localpath($this->join_paths($path));
        }
    }

    public function ls() {
        $resp = [];
        $fileList = glob($this->join_paths('/*'));
        foreach ($fileList as $filename){
            $resp[basename($this->localpath($filename))] = [
                "path" => $this->localpath($filename),
                "bucket" => $this->BUCKET,
                "is_file" => is_file($filename),
                "filesize" => filesize($filename),
            ];
        }
        return $resp;
    }

    public function createFileUploaded($source, $target) : bool {
        if (!$this->writable) return false;
        if ($target !== preg_replace("/[^a-zA-Z0-9_.\-]+/", "", $target) || ($target == "." && $target == ".." && $target == "")) {
            throw new Exception("[upldr.php] File name illegal! Only a-zA-Z0-9, - and _ . is allowed");
        }
        return move_uploaded_file($source["tmp_name"], $this->join_paths($target));
    }

    public function createFileGet($source, $target) {
        if (!$this->writable) return false;
        if ($target !== preg_replace("/[^a-zA-Z0-9_.\-]+/", "", $target) || ($target == "." && $target == ".." && $target == "")) {
            throw new Exception("[upldr.php] File name illegal! Only a-zA-Z0-9, - and _ . is allowed");
        }
        file_put_contents($this->join_paths($target), file_get_contents($source));
    }

    public function copy($source, $target) {
        if (!$this->writable) return false;
        if ($target !== preg_replace("/[^a-zA-Z0-9_.\-]+/", "", $target) || ($target == "." && $target == ".." && $target == "")) {
            throw new Exception("[upldr.php] File name illegal! Only a-zA-Z0-9, - and _ . is allowed");
        }
        copy($this->join_paths($source), $this->join_paths($target));
    }

    public function unlink($source) {
        if (!$this->writable) return false;
        unlink($this->join_paths($source));
    }

    public function mkdir($directory) : bool {
        if (!$this->writable) return false;
        //TODO: Don't do this that way...
        if ($directory !== preg_replace("/[^a-zA-Z0-9\-]+/", "", $directory)) {
            throw new Exception("[upldr.php] Directory name illegal! Only a-zA-Z0-9, - and _ is allowed");
        }
        $this->pmkdir($this->join_paths($directory));
        return true;
    }
    // Helpers
    // https://stackoverflow.com/questions/1091107/how-to-join-filesystem-path-strings-in-php#15575293
    private function join_paths() {
        $paths = array($this->media_store, $this->BUCKET, $this->PWD);
    
        foreach (func_get_args() as $arg) {
            if ($arg !== '') { $paths[] = $arg; }
        }
        $a = preg_replace('#/+#','/',join('/', $paths));
        $this->localpath($a);
        return $a;
    }

    private function localpath($path) : string {
        $a = $this->PWD;
        $this->PWD = "/";
        $new = str_replace($this->join_paths(), "/", $path);
        $this->PWD = $a;
        unset($a);
        if ($new == $path) {
            throw new Exception("[upldr.php] Invalid directory name!");
        }
        return $new;
    }

    private function pmkdir($directory) {
        @mkdir($directory, 0750, true);
    }
}

