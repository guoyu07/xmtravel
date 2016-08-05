<?php 
$root='http://you.ctrip.com';
$url='http://you.ctrip.com/sight/xiamen21.html';
$fp=fopen('./jingdian.docx', 'a');
while (true) {  
    if (trim($nextPageUrl)==$root) {
        exit('Have Done');
    }
    $ch=curl_init($url);
    $options=[
        CURLOPT_RETURNTRANSFER=>1,
        CURLOPT_HEADER=>0,
        CURLOPT_TIMEOUT=>30,
        CURLOPT_USERAGENT=>$_SERVER['HTTP_USER_AGENT']
        ];
    curl_setopt_array($ch, $options);
    $content=curl_exec($ch);
    $pattern='/<i class="sight"><\/i>\n(.*?)<a(.*?)href="(.*?)" title="(.*?)">(.*?)<\/a>(.*?)/';
    $nextpagepattern='/<a  class="nextpage" href="(.*?)">下一页<\/a>/';
    preg_match_all($pattern, $content,$name);
    foreach ($name[4] as $key=>$value) {
        $jingdian[$value]=$root.$name[3][$key];
        fwrite($fp, $value.'=>'.$root.$name[3][$key]."\n");
        getContent($value,$root,$fp,$jingdian[$value]);
    }
    fwrite($fp, "\n"); 
    preg_match_all($nextpagepattern, $content, $nextpage);
    $nextPageUrl=$root.$nextpage[1][0];
    $url=$nextPageUrl;
    echo 'Begin to collect->'.$nextPageUrl."\n";
}
function getContent($value,$root,$fp,$url)
{
    $ch=init($url);
    $content=curl_exec($ch);
    $contentPattern='/<divitemprop="description"class="text_style">(.*?)<\/div>/';
    preg_match_all($contentPattern, trimeStr($content), $description);
    fwrite($fp, '    '.strip_tags($description[1][1])."\n    ".strip_tags($description[1][3])."\n");
    $imgUrlPattern='/<\/ol><atarget="_blank"href="(.*?)"class="r_text">全部(.*?)张照片<\/a><\/div>/';
    preg_match_all($imgUrlPattern, trimeStr($content), $imgUrl);
    $imgUrl=$root.$imgUrl[1][0];
    $imgch=init($imgUrl);
    curl_setopt($imgch, CURLOPT_FOLLOWLOCATION, 1);
    $img=curl_exec($imgch);
    $getImgPattern='/<!--图片要输宽高--><imgsrc="(.*?)"alt="(.*?)"width="220"/';
    preg_match_all($getImgPattern, trimeStr($img),$imgResouce);
    for ($i=0; $i<count($imgResouce[1]); $i++) { 
        $path=preg_replace('/\*/',"", mb_convert_encoding(trimeStr('./images/'.$value.'/'.$value.$i.'.jpg'),'GB2312') ) ;
        echo '--------------------------------------------------'."\n";
        echo $path."\n";
        $im=file_get_contents($imgResouce[1][$i]);
        if (!file_exists($path)) {
            $patharr=explode('/', $path);
            if (!is_dir('./'.$patharr[1])) {
            mkdir('./'.$patharr[1]);
            }
             if (!is_dir('./'.$patharr[1].'/'.$patharr[2])) {
            mkdir('./'.$patharr[1].'/'.$patharr[2]);
             }
        }
        $imfp=fopen($path,'w');
        fwrite($imfp, $im);
        fclose($imfp);
    }
}

function trimeStr($a)
{
    $a=trim($a);
    $a=preg_replace("/\t/","",$a);
    $a=preg_replace("/\r\n/","",$a);
    $a=preg_replace("/\r/","",$a);
    $a=preg_replace("/\n/","",$a);
    $a=preg_replace("/ /","",$a);
    return $a;
}
function init($url)
{
    $ch=curl_init($url);
    $options=[
        CURLOPT_RETURNTRANSFER=>1,
        CURLOPT_HEADER=>0,
        CURLOPT_TIMEOUT=>30,
        CURLOPT_USERAGENT=>$_SERVER['HTTP_USER_AGENT']
        ];
    curl_setopt_array($ch, $options);
    return $ch;
}