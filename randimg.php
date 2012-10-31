<?php
class randimg {
  protected $images_dir;
  protected $stamps_dir;
  protected $result_dir;
  protected $images_files;
  protected $stamps_files;
  protected $stamps_count;
  protected $result_count;
  protected $img = array();
  
  public function __construct(){
    $this->images_dir = dirname(__FILE__).'/images/';
    $this->stamps_dir = dirname(__FILE__).'/stamps/';
    $this->result_dir = dirname(__FILE__).'/result/';
    # Перебираем картинки
    foreach(scandir($this->images_dir) as $i=>$file){
      if($this->img = $this->getSrc($this->images_dir.$file)){
        # Фильтр сдвиг
        $this->filterShift();
        # Фильтр наложения
        $this->filterStamp();
        unset($this->cfg_merge_count);
        # Сохранить картинку
        imagejpeg($this->img['src'],$this->img['file'],90);
        imagedestroy($this->img['src']);
        var_dump($file);
      }
    }
  }

  public function cfg(){
    $cfg = parse_ini_file(dirname(__FILE__).'/config.ini',true);
    return new randimgConfig($cfg);
  }
  
  public function getSrc($file){
    if(!is_file($file)) return;
    $name = implode(array_slice(explode('/',implode('.',array_slice(explode('.',$file),0,-1))),-1,1));
    $info = getimagesize($file);
    if($info[2] == 2) $src = imagecreatefromjpeg($file);
    elseif($info[2] == 3) $src = imagecreatefrompng($file);
    if(isset($src)){
      $new = imagecreatetruecolor($info[0],$info[1]);
      imagecopy($new,$src,0,0,0,0,$info[0],$info[1]);
      if(@imagedestroy($src)) return array('src'=>$new,'w'=>$info[0],'h'=>$info[1],'file'=>$this->result_dir.$name.'.jpg');
    }
  }

  # Сдвиг
  public function filterShift(){
    $cfg = $this->cfg()->shift();
    $new = imagecreatetruecolor($this->img['w'],$this->img['h']);
    imagecopy($new,$this->img['src'],$cfg['left'],$cfg['top'],$cfg['right'],$cfg['bottom'],$this->img['w'],$this->img['h']);
    imagedestroy($this->img['src']);
    $this->img['src'] = $new;
  }
  # Наложение
  public function filterStamp(){
    if(!isset($this->stamps)){
      foreach(scandir($this->stamps_dir) as $i=>$file){
        if($src = $this->getSrc($this->stamps_dir.$file)){ $this->stamps[] = $src; }
      }
      $this->stamps_count = count($this->stamps);
    }
    $cfg = $this->cfg()->merge();
    if(!isset($this->cfg_merge_count)) { $this->cfg_merge_count = $cfg['count']; }
    $i = rand(0,$this->stamps_count-1);
    $stamp = $this->stamps[$i];
    imagecopymerge($this->img['src'],$stamp['src'],$cfg['left'],$cfg['top'],0,0,$stamp['w'],$stamp['h'],$cfg['pct']);
    for($i=0;$i<=$this->cfg_merge_count;$i++){ 
      $this->cfg_merge_count--; 
      $this->filterStamp(); 
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
