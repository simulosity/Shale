<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Shale{
    
    public $level_data;
    public $level_view;
    public $cur_data;
    public $cur_level = 0;
    public $cur_view;
    
    public $parent_data = array();
    public $global_data = array();
    public $return_data = array();
    
    public $global_roster = array();
    
    public $cache_on = false;
    public $not_in_view = false;
    public $replaying = false;
    public $cache_data = array();
    public $cache_level = -1;
    
    public $search_str = '';

    
    public function __construct(){
        
        $this->CI =& get_instance();
        
    }
    
    /*
     * Use this function to open a tag section and
     * set some elementary values
     * 
     * 
     */
    
    public function open($view, $id='', $class='', $html=''){
        if(class_exists('Nog')){Nog::In();}

        if($this->cache_on){
            $this->cache_data[] = "O".$view;    
        }
        
        if(!$this->cur_view){
            $this->level_view = array();
            $this->level_data = array();
        }else{
            $this->level_view[$this->cur_level] = $this->cur_view;
            $this->level_data[$this->cur_level] = $this->cur_data;
            $this->cur_level += 1;
        }
        
        $this->cur_view = $view;
        $this->cur_data = array();
        
        if($id != ''){
            if(is_array($id)){
                foreach($id as $key => $value){
                    $this->set($key, $value);
                }
            }else{
                $this->set('id', $id);
            }
        }
            
        if($class != ''){
            $this->set('class', $class);
        }
        
        if($html != ''){
            $this->set('html', $html);
        }
        

        if(class_exists('Nog')){Nog::Out();}
        
    }
    
    /*
     * 
     * set attribute values for a tag section
     * 
     */
    
    public function set($att, $value, $search=false){
        
        if(class_exists('Nog')){Nog::In();}
        
        if($this->cache_on && $this->not_in_view){
            if($search){
                $this->cache_data[] = "S".$att."$".$value;
            }else{
                $this->cache_data[] = "s".$att."$".$value;
            }
        }

        $this->cur_data[$att][] = $value;
        
        if($search){
            $this->search_str .= strip_tags($value).' ';
        }

        if(class_exists('Nog')){Nog::Out();}
    }
    
    /*
     * 
     * set attribute values that will be used for the global scope (page)
     * 
     * 
     */
    
    public function gset($att, $value, $unique_id = false){
        if(class_exists('Nog')){Nog::In();}
        
        if(!$unique_id && !in_array($unique_id, $this->global_roster)){
            $this->global_data[$att][] = $value;
        }

        if(class_exists('Nog')){Nog::Out();}
    }
    
    /*
     * 
     * Set attribute values for the tag section's parent section
     * 
     */
    
    public function pset($att, $value){
        if(class_exists('Nog')){Nog::In();}

        $this->parent_data[$att][] = $value;
        
        if(class_exists('Nog')){Nog::Out();}
    }
    
    /*
     * Used by a view to set return valuse for the close function
     * 
     */
    
    
    public function rset($att, $value){
        if(class_exists('Nog')){Nog::In();}

        $this->return_data[$att][] = $value;
        
        if(class_exists('Nog')){Nog::Out();}
    }

    
    /*
     * Closes a tag section and either sends that results to the parent section
     * or closes out the web page
     * 
     */
    
    public function close($view, $note=''){
        if(class_exists('Nog')){Nog::In();}
        
        if($this->cache_on){
            $this->cache_data[] = "C".$view;    
        }
        
        if($view != $this->cur_view){
        
            $this->CI->output->append_output('open('.$this->cur_view.') != close('.$view.')::'.$note);
            
        }else if($this->cur_level > 0){
            
            if(!isset($this->cur_data['html'])){
                $this->cur_data['html'][] = "&nbsp;";
            }

            if(!file_exists(APPPATH.'views/'.$view.'.php')){
                
                $temp = '<'.$view;
                foreach($this->cur_data as $key=>$value){
                    if($key != 'html'){
                        $temp .=  $this->get($key, ' ', " $key='", "'");
                    }
                }
                $temp .= '>'.$this->get('html', PHP_EOL).'</'.$view.'>';
                
            }else{
                
                $this->not_in_view = false;
                $temp = $this->CI->load->view($view, array('CI' => $this->CI, 'Shale' => $this, 'Data'=>$this->cur_data), true);
                $this->not_in_view = true;
            }

            $this->cur_level -= 1;
            $this->cur_view = $this->level_view[$this->cur_level];
            $this->cur_data = $this->level_data[$this->cur_level];
            
            $this->set('html', $temp);
            
            foreach($this->parent_data as $key => $value){
                $this->set($key, $value);
            }
            
            $this->parent_data = array();
            
        }else{
            
            foreach($this->global_data as $key => $value){
                $this->set($key, $value);
            }
            
            $this->not_in_view = false;
            $this->CI->load->view($view, array('CI' => $this->CI, 'Shale' => $this, 'Data'=>$this->cur_data));
            $this->not_in_view = true;
            
        }
        
        $ret = $this->return_data;
        $this->return_data = array();
        
        if(class_exists('Nog')){Nog::Out();}
        
        return $ret;
        
    }
    
    
    public function place($view, $id='', $class='', $html=''){
        if(class_exists('Nog')){Nog::In();}
        $this->open($view, $id, $class, $html);
        $this->close($view);
        if(class_exists('Nog')){Nog::Out();}
    }
    
    public function cache($name, $time=21600){
        if(class_exists('Nog')){Nog::In();}
        if($time < 60){
            $time = $time * 3600;
        }
        
        
        if(class_exists('Nog')){Nog::Out();}
    }
     
    public function log(){
        if(class_exists('Nog')){Nog::In();}
        
        if(!defined('PAGE')){

            $page = uri_string();

            define('PAGE', $page);
            define('URL', current_url());

            $history = $this->CI->session->userdata('history');
            if($history){
                $history = $page.'|'.$history;
            }else{
                $history = $page.'|';
            }

            $history = substr($history, 0, 2000);

            $this->CI->session->set_userdata('history', $history);

        }
        
        if(class_exists('Nog')){Nog::Out();}
    }
    
    
    public function back($not = '', $redirect = false){
        if(class_exists('Nog')){Nog::In();}

        $history = explode('|', $this->CI->session->userdata('history'));

        if($not == ''){
            $not = PAGE;
        }

        foreach($history as $entry){
            if(strpos($entry, $not) === false){
                if($redirect){
                    redirect('/'.$entry, 'refresh');
                }else{
                    if(class_exists('Nog')){Nog::Out();}
                    return $entry;
                }
            } 
        }
        
        if(class_exists('Nog')){Nog::Out();}
    }
    
        
    
    public function get($key, $sep = "", $pre="", $suf=""){
        if(class_exists('Nog')){Nog::In();}
        
        $str = '';
        $var = $this->cur_data;
        
        if(isset($var[$key])){

            $text = $var[$key];
            $str .= $pre;
            $addit = false;
            
            foreach($text as $entry){
                if($addit){
                    $str .=  $sep;
                }
                $str .=  $entry;
                $addit = true;
            }
            
            $str .=  $suf;
            if(class_exists('Nog')){Nog::Out();}
            return $str;
                
        }else{
            if(class_exists('Nog')){Nog::Out();}
            return '';
        }
        
        
    }
    
    public function att($include = ''){
        
        if(class_exists('Nog')){Nog::In();}
              
        $str =   $this->get('id', '', " id='", "'");
        $str .=  $this->get('class', ' ', " class='", "'");
        $str .=  $this->get('style', ' ', " style='", "'");

        if($and !== ''){
            if(is_array($and)){
                foreach($include as $value){
                    $str .=  $this->get($value, ' ', " $value='", "'");
                }
            }else{
                $str .=  $this->get($include, ' ', " $include='", "'");
            }
            
        }

        $str .=  $this->get('att', ' ', ' ', '');

        if(class_exists('Nog')){Nog::Out();}
        
        return $str;
        
    }
    

}