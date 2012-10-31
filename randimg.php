<?php
class randimg {
  protected $images_dir;
  protected $stamps_dir;
  protected $result_dir;
  protected $images_files;
  protected $stamps_files;
  protected $stamps_count;
  protected $result_count;
  protected $images = array();
  
  public function __construct(){
    $this->images_dir = dirname(__FILE__).'/images/';
    $this->stamps_dir = dirname(__FILE__).'/stamps/';
    $this->result_dir = dirname(__FILE__).'/result/';
    $this->images = $this->getSrc($this->images_dir);
    $this->stamps = $this->getSrc($this->stamps_dir);
    $this->images_count = count($this->images);
    $this->stamps_count = count($this->stamps);
    # Сдвиг
    $this->filterShift();
    # Наложение штампов
    $this->filterStamp();
  }

  public function cfg(){
    $cfg = parse_ini_file(dirname(__FILE__).'/config.ini',true);
    return new randimgConfig($cfg);
  }
  
  public function getSrc($dir){
    $scan = scandir($dir);
    foreach($scan as $i=>$file){
      $name = implode('.',array_slice(explode('.',$file),0,-1));
      $file = $dir.$file;
      if(is_file($file)){
        $info = getimagesize($file);

        if($info[2] == 2) $src = imagecreatefromjpeg($file);
        elseif($info[2] == 3) $src = imagecreatefrompng($file);
        else continue;
        
        $result[] = array('src'=>$src,'w'=>$info[0],'h'=>$info[1],'file'=>$this->result_dir.$name.'.jpg');
      }
    }
    return $result;
  }

  # Сдвиг
  public function filterShift(){
    foreach($this->images as $i=>$img){
      $cfg = $this->cfg()->shift();
      $new = imagecreatetruecolor($img['w'],$img['h']);
      imagecopy($new,$img['src'],$cfg['left'],$cfg['top'],$cfg['right'],$cfg['bottom'],$img['w'],$img['h']);
      $this->images[$i]['src'] = $new;
    }
  }
  # Наложение
  public function filterStamp(){
    foreach($this->images as $i=>$img){
      $cfg = $this->cfg()->merge();
      if(!isset($this->cfg_merge_count)) $this->cfg_merge_count = $cfg['count'];
      $i = rand(0,$this->stamps_count-1);
      $stamp = $this->stamps[$i];
      imagecopymerge($img['src'],$stamp['src'],$cfg['left'],$cfg['top'],0,0,$stamp['w'],$stamp['h'],$cfg['pct']);
      $this->images[$i]['src'] = $img['src'];
      for($i=0;$i<=$this->cfg_merge_count;$i++){ $this->cfg_merge_count--; $this->filterStamp(); }
    }
  }

  public function __destruct(){
    foreach($this->images as $i=>$img){
      var_dump($img['file']);
      @imagejpeg($img['src'],$img['file'],90);
      @imagedestroy($img['src']);
    }
  }
  
}

class randimgConfig {
  protected $cfg;
  public function __construct($cfg){
    $this->cfg = $cfg;
  }
  
  public function shift(){
    foreach($this->cfg['shift'] as $i=>$cfg){
      $rand = explode(',',$cfg);
      $result[$i] = rand($rand[0],$rand[1]);
    }
    return $result;
  }
    
  public function merge(){
    foreach($this->cfg['merge'] as $i=>$cfg){
      $rand = explode(',',$cfg);
      $result[$i] = rand($rand[0],$rand[1]);
    }
    return $result;
  }
  
}

$r = new randimg;
?>
