<?php
/*
* 上传类
* 单文件方法Upload::upload() 返回上传后的文件路径字符串
* 多文件方法Upload::uploads() 返回二维数组 $rets = array(array('mes':'上传成功','dest':'uploads/d51dcf262940811e36da3d6f9b75ddf1.jpg'),array()....);
*/
class Upload {
    protected $fileName;
    protected $maxSize;
    protected $allowMime;
    protected $ext;
    protected $allowExt;
    protected $uploadPath;
    protected $imgFlag;
    protected $fileInfo;
    protected $error;

    /**
     * @brief
     *
     * @param $fileName
     * @param $uploadPath
     * @param $imgFlag
     * @param $maxSize
     * @param $allowExt
     * @param $allowMime
     *
     * @return
     */
    public function __construct($fileName='myFile',$uploadPath='uploads',$imgFlag='true',$maxSize=2097152,$allowExt=array('jpeg','jpg','gif','png'),$allowMime=array('image/jpeg','image/jpg','image/gif','image/png')){
        $this->fileName = $fileName;
        $this->uploadPath = $uploadPath;
        $this->imgFlag = $imgFlag;
        $this->maxSize = $maxSize;
        $this->allowExt = $allowExt;
        $this->allowMime = $allowMime;
        if(isset($_FILES[$fileName])) $this->fileInfo = $_FILES[$fileName];
    }
    //检查错误号
    protected function checkError(){
        if($this->fileInfo['error']===UPLOAD_ERR_OK){
            return true;
        }else{
            switch($this->fileInfo['error']){
                case 1:
                    $this->error = '超过了服务器限制大小'; //超过了php.ini配置文件中upload_max_filesize选项的值
                    break;
                case 2:
                    $this->error = '超过了表单中MAX_FILE_SIZE设置的值';
                    break;
                case 3:
                    $this->error = '文件被部分上传';
                    break;
                case 4:
                    $this->error = '没有选择上传的文件';
                    break;
                case 6:
                    $this->error = '没有找到临时目录';
                    break;
                case 7:
                    $this->error = '文件不可写';
                    break;
                case 8:
                    $this->error = '由于PHP的扩展程序中断文件上传';
                    break;
                default:
                    $this->error = '文件上传出错';
            }
            return false;
        }
    }
    //检查大小
    protected function checkSize(){
        if($this->fileInfo['size']>$this->maxSize){
            $this->error = '上传文件过大';
            return false;
        }
        return true;
    }
    //检查文件扩展名
    protected function checkExt(){
        $this->ext = $this->getExt($this->fileInfo['name']);
        if(!in_array($this->ext,$this->allowExt)){
            $this->error = '非法的文件扩展名';
            return false;
        }
        return true;
    }
    //检查文件mime
    protected function checkMime(){
        if(!in_array($this->fileInfo['type'],$this->allowMime)){
            $this->error = '不允许的文件类型';
            return false;
        }
        return true;
    }
    //检查图片真实性
    protected function checkTrueImg(){
        if(!@getimagesize($this->fileInfo['tmp_name'])){
            $this->error = '不是真实的图片类型';
            return false;
        }
        return true;
    }
    //检查是否是通过HTTP POST方式上传
    protected function checkHTTPPost(){
        if(!is_uploaded_file($this->fileInfo['tmp_name'])){
            $this->error = '不是通过HTTP POST方式上传的文件';
            return false;
        }
        return true;
    }
    //检查上传目录
    protected function checkUploadPath(){
        if(!file_exists($this->uploadPath)){
            mkdir($this->uploadPath,0777,true);
            chmod($this->uploadPath,0777);
        }
    }
    //显示错误
    protected function showError(){
        exit('<span style="color:red">'.$this->error.'</span>');
    }
    //单文件上传
    public function upload(){
        if($this->checkError()&&$this->checkSize()&&$this->checkExt()&&$this->checkMime()&&$this->checkTrueImg()&&$this->checkHTTPPost()){
            $this->checkUploadPath();
            $uniName = $this->getUniName();
            $destination = $this->uploadPath.'/'.$uniName.'.'.$this->ext;
            if(@move_uploaded_file($this->fileInfo['tmp_name'],$destination)){
                return $destination;
            }else{
                $this->error = '移动文件失败';
                $this->showError();
            }

        }else{
            $this->showError();
        }
    }
    //获取文件信息
    protected function getFiles(){
        $i = 0;
        foreach($_FILES as $file){
            if(is_string($file['name'])){
                $files[$i++] = $file;
            }elseif(is_array($file['name'])){
                foreach($file['name'] as $key=>$value){
                    $files[$i]['name'] = $file['name'][$key];
                    $files[$i]['tmp_name'] = $file['tmp_name'][$key];
                    $files[$i]['type'] = $file['type'][$key];
                    $files[$i]['size'] = $file['size'][$key];
                    $files[$i]['error'] = $file['error'][$key];
                    $i++;
                }
            }
        }
        return $files;
    }
    /*得到文件的扩展名*/
    function getExt($filename){
        return strtolower(pathinfo($filename,PATHINFO_EXTENSION));
    }
    /*获得唯一字符串*/
    function getUniName(){
        return md5(uniqid(microtime(true),true));
    }
    //多文件上传
    public function uploads(){
        $files = $this->getFiles();
        foreach($files as $file){
            $ret['mes'] = $ret['dest'] = '';
            $this->fileInfo = $file;
            if($this->checkError()&&$this->checkSize()&&$this->checkExt()&&$this->checkMime()&&$this->checkTrueImg()&&$this->checkHTTPPost()){
                $this->checkUploadPath();
                $uniName = $this->getUniName();
                $destination = $this->uploadPath.'/'.$uniName.'.'.$this->ext;
                if(@move_uploaded_file($this->fileInfo['tmp_name'],$destination)){
                    $ret['dest'] = $destination;
                    $this->error = '上传成功';
                }else{
                    $this->error = '移动文件失败';
                }

            }
            $ret['mes'] = $this->error;
            $rets[] = $ret;
        }
        return $rets;
    }
}
