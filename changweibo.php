<?php        #changweibo.php
class changweibo
{
  public $width;
  public $fontsize;
  public $angle;
  public $fontface;
  public $string;
  
  public function __construct($width,$fontsize,$angle,$fontface,$string)
  {
    $this->width    = $width;
    $this->fontsize = $fontsize;
    $this->angle    = $angle;
    $this->fontface = $fontface;
    $this->string   = $string;
  }
  
    
  mb_internal_encoding("UTF-8"); // 设置编码
  
  public function autowrap($fontsize, $angle, $fontface, $string, $width) 
  {
    // 这几个变量分别是 字体大小, 角度, 字体名称, 字符串, 预设宽度 
    $content = "";
	$height=0;
  
    // 将字符串拆分成一个个单字 保存到数组 letter 中
    for ($i=0;$i<mb_strlen($string);$i++) 
	  {
        $letter[] = mb_substr($string, $i, 1);
    }
  
    foreach ($letter as $l) 
	  {
        $teststr = $content." ".$l;
        $testbox = imagettfbbox($fontsize, $angle, $fontface, $teststr);
        // 判断拼接后的字符串是否超过预设的宽度
        if (($testbox[2] > $width) && ($content !== ""))
        {
            $content .= "\n";
        }
		    $height=$testbox[3];
        $content .= $l;
    }
	//返回格式化的文本和格式化文本的高度
    return array($content,$height);
  }
  
  public function createImage()
  {
    list($text,$height) = autowrap($this->fontsize,$this->angle,$this->fontface,$this->string,$this->width-10);
    $bg = imagecreatetruecolor($this->width,$height+50);
    $white = imagecolorallocate($bg,255,255,255);
    $black = imagecolorallocate($bg,0,0,0);
    imagefill($bg,0,0,$white);
    imagettftext($bg,$this->fontsize,$this->angle,10,30,$black,$this->fontface,$this->string);
    ob_clean();
    Header("Content-type: image/png");
    imagepng($bg);
    imagedestroy($bg);
  }
}
