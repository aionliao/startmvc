<?php
/**
 * StartMVC超轻量级PHP开发框架
 *
 * @author    Shao Bing QQ858292510
 * @copyright Copyright (c) 2020-2021
 * @license   StartMVC 遵循Apache2开源协议发布，需保留开发者信息。
 * @link      http://startmvc.com
 */
 
namespace Startmvc\Lib;
use Startmvc\Lib\Http\Request;

abstract class Controller extends Start
{
	public $assign=[];
    /**
     * 为模板对象赋值
     */
    public function assign($name, $data=null)
    {
	    if(is_array($name)){
		    $this->assign = $name;
	    }else{
		    $this->assign[$name] = $data;
	    }
	    //print_r($this->assign);
    }


    protected function view($template = '')
    {
        if (is_array($template)) {
            $template = APP_PATH . '/' . $template[0] . '/View/' . $template[1] . '.php';
        } else {
            if ($template == '') {
                $template = CONTROLLER . '_' . ACTION;
            }
            $template = APP_PATH . '/' . (MODULE != '' ? MODULE . '/' : '') . 'View/' . $template . '.php';
        }
        if (file_exists($template)) {
            $contents = file_get_contents($template);
            $contents = $this->tp_engine($contents);

        //header('Content-Type:text/html; charset=utf-8');
        $this->show($contents,$template);
        } else {
            $this->content('视图文件不存在：' . $template);
        }
    }
    private function tp_engine($c)
    {
        preg_match_all('/{include (.+)}/Ui', $c, $include);
        foreach ($include[1] as $inc) {
            $inc_array = explode('|', $inc);
            if (isset($inc_array[1])) {
                $inc_file = APP_PATH . '/' . $inc_array[1] . '/View/' . $inc_array[0] . '.php';
            } else {
                $inc_file = APP_PATH . '/' . (MODULE != '' ? MODULE . '/' : '') . '/View/' . $inc_array[0] . '.php';
            }
            $inc_content = file_get_contents($inc_file);
            $c = str_replace('{include ' . $inc . '}', $inc_content, $c);
        }
        $c = str_replace('<?=', '<?php echo ', $c);
        $c = str_replace('<?', '<?php ', $c);
        $c = str_replace('<?php php', '<?php', $c);
        return $c;
    }
    protected function show($content,$template)
    {
        $file_name=(MODULE != '' ? MODULE . '_' : '') .CONTROLLER . '_' . ACTION;
        if(!is_dir(TEMP_PATH)){
	        mkdir(TEMP_PATH,0777);
	        chmod(TEMP_PATH,0777);
        }
        $runtime_file = TEMP_PATH . $file_name . '.php';
        if(!file_exists($runtime_file) || filemtime($runtime_file) < filemtime($template)) {
	        $of = fopen($runtime_file, 'w+');
	        fwrite($of, $content);
	        fclose($of);
        }
        if(is_object($this->assign)) {
			extract((array)$this->assign);
	    }else{
			extract($this->assign);
	    }
		header('Content-Type:text/html; charset=utf-8');
		include_once($runtime_file);
    }
    public function content($content)
    {
        header('Content-Type:text/plain; charset=utf-8');
        echo $content;
    }
    protected function success($msg='',$url='',$data=[],$ajax=false)
    {
       $this->response(1,$msg,$url,$data,$ajax);
    }
    protected function error($msg='',$url='',$data=[],$ajax=false)
    {
       $this->response(0,$msg,$url,$data,$ajax);
    }
    protected function response($code='',$msg='',$url='',$data=[],$ajax=false)
    {
	    if($ajax || Request::isAjax()){
		    $data=[
			    'code'=>$code,//1-成功 0-失败
			    'msg'=>$msg,
			    'url'=>$url,
			    'data'=>$data,
		    ];
	    	$this->json($data);
	    }else{
	        include '../startmvc/Lib/location.php';
	        exit();
	    }

    }

    
    protected function json($data)
    {
        header('Content-Type:application/json; charset=utf-8');
        //echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit(json_encode($data, JSON_UNESCAPED_UNICODE));
    }


    
    protected function redirect($url)
    {
        header('location:' . $url);
        exit();
    }
    protected function notFound()
    {
        header("HTTP/1.1 404 Not Found");  
        header("Status: 404 Not Found");
    }
    public function __call($fun, $arg)
    {
        $this->notFound();
    }
}
