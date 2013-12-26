<?php      #Changweibo.php

/**
*  author:    ta_shuo
*  Program:   Use GD of PHP to create Changweibo
*  History:   
*         create:      2013/12/11 最基本的文本换行功能
*         change_1:    2013/12/26 增加数字，单词识别功能，还有行首符号的识别，但是行首符号的识别还未完善
*/


//第一次修改后的版本
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
    $this->text     = $text;
  }
  
  //截取字符串，实现单词，数字的整理，换行
  public function autowrap() 
  {
    mb_internal_encoding("UTF-8");       // 设置通用编码，否则中文容易乱码 
    $total_content   = "";               //所有的格式化的字符
	$line_content    = "";               //每一行的字符串
	$tmpLetter       = "";               //缓存的字符串
	$tmpFlag         = false;            //是否利用缓存
	$flagMark        = "";               //缓存标记,'num'或'word'
  
    // 将字符串拆分成一个个单字 保存到数组 letter 中
    for ($i=0;$i<mb_strlen($this->text);$i++) 
    {
        $letter[] = mb_substr($this->text, $i, 1);
    }
	
    foreach ($letter as $word) 
    {
	    //数字的处理
	    if($this->isNum($word))
		{
		  //如果当前有字母的缓存,则对缓存字符串进行处理
		  if($flagMark =="word")
		  {
		    
		    $teststr   = $line_content.$tmpLetter;
            $testbox = imagettfbbox($this->fontsize, $this->angle, $this->fontface, $teststr);
			
			//如果宽度过大则进行换行处理：将line_content添加换行符后，连接到total_content，然后将当前的缓存字符串赋值给line_content;
			if($testbox[2] > ($this->width - 60))
			{
			  $line_content  .= "\n";
			  $total_content  = $total_content.$line_content;
			  $line_content   = $tmpLetter;
			}
			
			//如果不用换行则直接将缓存字符串连接到line_content;
			else
			{
			  $line_content  .= $tmpLetter;
			}
			//记得最后要将缓存的字符串清空
			$tmpLetter = "";
		  }
          
		  //将当前取出的字符存储于缓存字符串中，并将缓存标志打开为"num"
		  $tmpLetter .= $word;
		  $tmpFlag    = true;
		  $flagMark   = "num";	  
		}
		
		//字母的处理，处理流程同数字
		else if($this->isWord($word))
		{
		  //同数字的处理
		  if($flagMark == "num")
		  {
		    $teststr   = $line_content.$tmpLetter;
            $testbox = imagettfbbox($this->fontsize, $this->angle, $this->fontface, $teststr);
			
			if($testbox[2] > ($this->width - 60))
			{
			  $line_content  .= "\n";
			  $total_content  = $total_content.$line_content;
			  $line_content   = $tmpLetter;
			}
			else
			{
			  $line_content  .= $tmpLetter;
			}
			
			$tmpLetter = "";
		  }
		 
		  $tmpLetter .= $word;
		  $tmpFlag    = true;
		  $flagMark   = "word";		  
		}
		
		//非数字，字母的处理
		else
		{
		  //如果存在缓存,则先对缓存字符串进行处理,并将缓存字符串清空，否则直接对取出的字符进行处理
		  if($tmpFlag)    
		  {
  		    $add_word  = $tmpLetter; 
			$tmpLetter = "";
		  }
		  else            
		    $add_word  = $word;
		  
		  $teststr     = $line_content.$add_word;
		  $testbox     = imagettfbbox($this->fontsize, $this->angle, $this->fontface, $teststr);
		  
		  //换行的处理,需要注意的是line_content的内容必须包含从每一行开始位置的内容，因为一直是用line_content来判断是否需要换行
	      if($testbox[2] > ($this->width - 60))
		  {
		    //如果当前没有缓存，处理的是取出的字符，并且该字符时符号，则先将该字符连接到line_content后再进行换行
		    if(!$tmpFlag && $this->isFuhao($add_word))
			{
			  $line_content .= $add_word;
			  $add_word      = "";
			}
		    $line_content  .= "\n";
		    $total_content  = $total_content.$line_content;
		    $line_content   = $add_word;
		  }
		  //不换行的话直接将相应的字符串连接到line_content
		  else
		  {
		    $line_content  .= $add_word;
		  }
		  
		  //如果当前用的是缓存字符串，则记得要将取出的字符连接到line_content
		  if($tmpFlag)    
		      $line_content .= $word;
		  
		  //并且要将缓存标志关闭
		  $tmpFlag = false;
		}
		
		
    }
	//记得要将可能的最后一行的数据添加到total_content
	$total_content .= $line_content;
	//根据total_content来获取文本的高度
	$arr = imagettfbbox($this->fontsize, $this->angle, $this->fontface, $total_content);
    //返回格式化的文本和格式化文本的高度
    return array($total_content,$arr[3]);
  }
  
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
    imagettftext($this->bg,$this->fontsize,$this->angle,30,30,$black,$this->fontface,$text);
	return $this->bg;
  }
  
  //判断字符是否是字母
  public function isWord($word)
  {
    $flag=false;
	if(ord($word)>=65 && ord($word)<= 90)
	    $flag=true;
    if(ord($word)>=97 && ord($word)<= 122)
	    $flag=true;
    return $flag;
  }
  
  //判断字符是否是数字
  public function isNum($word)
  {
    return ord($word)>=48&&ord($word)<=57;
  }
  
  //判断字符是否是中文
  public function isChinese($word)
  {
    return ord($word)>0xa0&&ord($word)<0xfe;
  }
  
  //判断是否是不能放置于行首的符号,即非数字，非字母，非汉字，非奇特特殊符号
  public function isFuhao($word)
  {
    $arr_valid_fuhao = array("“","(","{","[","【","《");
    return !($this->isNum($word) || $this->isWord($word) || $this->isChinese($word)) && !in_array($word,$arr_valid_fuhao);
  }
  
  
}

  $text="如果上查你在置文件是下安装配在置文件如果上查你在置文件是下安件是下安装配在置文件如果上查你在置文装配在是下安装配在置文你在置文件是下安装配在置文件如果上查你在置文件是下安件是下安装配在置文件如果上查你在置文装配在是下安装配在置文件是下安装配置注释似乎默认件是下安装配置注释似乎默认述7536757方法还不行，请检gsertgsrthsdrt查你在cervsdrthsdgferthgty,,.1569854447656547478788325编译gd库,.$%&时是否添加了4545545454545454545–enable-gd-jisfsdrgthyrhj-conv选项，此选frargtagtagr项是为了让gdertgsrthsdrt查你在cervsdrthsdgferthgty””””“”1569854447656547478788325编译gd库,.$%&时是否添加了45455454时是ertgsrthsdrt查你在“”cervsdrthsdgferthgty,,.1569854447656547478788325编译gd库,.$%&时是否添加了45455454545454否添加了45455454545454估计greg主要是针对Unix75675下安装配置php75654634573263467545454库支持日文编码的42653476字库，请greg取消此选grgawegrae项并746745重新编译。此方法gaerhsdrt查你在cervsdrthsdgf325编译gd库,.$%&时是否添加了45455454545454否添加了45455454545454估计greg主要是针对Unix75675下安装配置php75654634573263467%&时是否添加了45455454545454否添加了45gae我没验证过，ertgsrthsdrt查你在cervsdrthsdgferthgty,,.1569854447656547478788325编译gd库,.$%&时是ertgsrthsdrt查你在cervsdrthsdgferthgty,,.1569854447656547478788325编译php,,.15698544476565474787885454545454估计greg主要是针对Unix75675下安装配置php7565463457326346773环境。Windows环境一般不会erge出现这种情况，73653675似乎默认PHP配greqgegreg置文件是注释ertgsrthsdrt查你在cervsdrthsdgf325编译gd库,.$%&时是否添加了45455454545454否添加了4545545454545gd库,p75654634573263467%&时是否添加了45455454545454否添加了45455454545454估计greg主要是针对Unix75675下安装配置php,,.15698544476565474787885454545454估计greg主要是针对Unix75675下安545454否添加了45gae我没验证过，ertgsrthsdrt查你在cervsdrthsdgferthgty,,.1569854447656547478788325编译gd库,.$%&时是ertgsrthsdrt查你在cervsdrthsdgferthgty,,.1569854447656547478788325编译装配置php7565463457326346773环境。Windows环境一般不会erge出现这种情况，73653675似乎默认PHP配greqgegreg置文.3263467545454库支持日文编码的42653476字库，请greg取消此选grgawegrae项并746745重新编译。此方法gaerhsdrt查你在cervsdrthsdgf325编译gd库,.$%&时是否添加了45455454545454否添加了45455454545454估计greg主要是针对Unix75675下安装配置php75654634573263467%&时是否添加了45455454545454否$时是ertgsrthsdrt查你在cervsdrthsdgferthgty53675似乎默认PHP配greqgegreg置文件是注释ertgsrthsdrt查你在cervsdrthsdgf325编译gd库45454估计greg主要是针对Unix75675下安装配置php,,.15698544476565474787885454545454估计gregphp,,.15698544476565474787885454545454估计greg主要是针对Unix75675下安装配置php7565463457326346773环境。Windows环境一般不会erge出现这种情况，73653675似乎默认PHP配greqgegreg置文件是注释ertgsrthsdrt查你在cervsdrthsdgf325编译gd库,.$%&时是否添加了45455454545454否添加了45php,,.15698544476565474787885454545454估计greg主要是针对Unix75675下安装配置php7565463457326346773环境。Windows环境一般不会erge出现这种情况，73653675似乎默认PHP配greqgegreg置文件是注释ertgsrthsdrt查你在cervsdrthsdgf325编译gd库,.$%&时是否添加了45455454545454否添加了454554545454545545454545主要是针对Unix75675下安装配置php7565463457326346773环境。Windows环境一般不会,.$%&时是否添加了45455454545454否添加了45455454545454估计greg主要是针对Unix75675下安装配置php75654634573263467%&时是否添加了45455454545454否添加了45455454545454估计greg主要是针对Unix75675下安装配置php,,.15698544476565474787885454545454估计greg主要是针对Unix75675下安装配置php7565463457326346773环境。Windows环境一般不会erge出现这种情况，73653675似乎默认PHP配greqgegreg置文件是注释ertgsrthsdrt查你在cervsdrthsdgf325编译gd库,.$%&时是否添加了45455454545454否添加了45455454545454估计greg主要是针对Unix75675下安装配置php75654634573263467%&时是否添加了45455454545454否添加了45455454545454估计greg主要是针对Unix75675下安装配置php7565463457326346773环境。Windows环境一般不会erge出现这种情况，73653675似乎默认PHP配greqgegreg置文件是注释ertgsrthsdrt查你在cervsdrthsdgferthgty,,.1569854447656547478788325编译gd库,.$%&时是否添加了45455454545454掉的";
  //字体文件路径要正确
  $Obj=new Changweibo(800,12,0,"simsun.ttc",$text);
  $image=$Obj->createImage();
  
  ob_clean();
  Header("Content-type: image/png");
  imagepng($image);
  imagedestroy($image);
