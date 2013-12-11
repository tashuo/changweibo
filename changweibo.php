<?php      #Changweibo.php

/**
*  author:    ta_shuo
*  Program:   Use GD of PHP to create Changweibo
*  History:   2013/12/11
*/


class Changweibo
{
  public $width;        //预设的画布宽度
  public $fontsize;     //预设的字体大小
  public $angle;        //预设的填充角度，默认为0，即水平方向
  public $fontface;     //字体类型，如果截取中文字符串的话必须用特定的中文字体
  public $text;         //操作的字符串
  public $bg;           //生成的图像对象
  
  //初始化
  public function __construct($width,$fontsize,$angle,$fontface,$text)
  {
    $this->width    = $width;
    $this->fontsize = $fontsize;
    $this->angle    = $angle;
    $this->fontface = $fontface;
    $this->text   = $text;
  }
  
  //截取字符串，实现换行
  public function autowrap() 
  {
    mb_internal_encoding("UTF-8"); // 设置通用编码，否则中文容易乱码 
    $content = "";
    $height = 0;
  
    // 将字符串拆分成一个个单字 保存到数组 letter 中
    for ($i=0;$i<mb_strlen($this->text);$i++) 
    {
        $letter[] = mb_substr($this->text, $i, 1);
    }
    foreach ($letter as $l) 
    {
        $teststr = $content." ".$l;
        $testbox = imagettfbbox($this->fontsize, $this->angle, $this->fontface, $teststr);
        // 判断拼接后的字符串是否超过预设的宽度
        if (($testbox[2] > $this->width-10) && ($content !== ""))
        {
            $content .= "\n";
        }
		//获取填充字符下界的值，即高度
        $height=$testbox[3];
        $content .= $l;
    }
    //返回格式化的文本和格式化文本的高度
    return array($content,$height);
  }
  
  //生成填充后的图像文件
  public function createImage()
  {
    list($text,$height) = $this->autowrap();
	//根据格式化后字符的高度初始化画布
    $this->bg = imagecreatetruecolor($this->width,$height+50);
    $white = imagecolorallocate($this->bg,255,255,255);
    $black = imagecolorallocate($this->bg,0,0,0);
	//填充画布背景为白色
    imagefill($this->bg,0,0,$white);
	//将格式化后的字符串填充到画布中
    imagettftext($this->bg,$this->fontsize,$this->angle,10,30,$black,$this->fontface,$text);
	return $this->bg;
  }
}


  //以下为测试
  $text="如果上述方法还不行，请检查你在编译gd库时是否添加了–enable-gd-jis-conv选项，此选项是为了让gd库支持日文编码的字库，请取消此选项并重新编译。此方法我没验证过，估计主要是针对Unix下安装配置php环境。Windows环境一般不会出现这种情况，似乎默认PHP配置文件是注释掉的";
  //字体文件路径要正确
  $Obj=new Changweibo(300,12,0,"simsun.ttc",$text);
  $image=$Obj->createImage();
  //清除缓冲区内容，设置正确的头部信息
  ob_clean();
  Header("Content-type: image/png");
  imagepng($image);
  imagedestroy($image);
