<?php
/**
 * Base field importer
 *
 * @author codeko
 */
class DrupalBaseField {
    var $field_map=array();
    var $current_field_map=array();
    var $ignore_cols=array("entity_type","bundle","deleted","entity_id","revision_id","language","delta");
    static $ignore_names=array("body");
    var $post_data=FALSE;
    var $field_data=FALSE;
    var $field_info=FALSE;
    var $repeater_post=FALSE;
    var $repeater_post_count=0;
    var $repeater_post_name=FALSE;
    var $drupal_importer;
    function __construct($field_info,$drupal_importer,$field_map=FALSE) {
        $this->field_info=$field_info;
        $this->drupal_importer=$drupal_importer;
        if($field_map){
            $this->field_map = $field_map;
        }
    }
    
    public static function registerBaseFields(){
        add_filter("drupal2wp_get_field_importer", array( 'DrupalBaseField', 'registerBaseFieldsCallback'));
    }
    
    public static function registerBaseFieldsCallback($currentFieldImporter){
        $fi=apply_filters('drupal2wp_process_field_info',$currentFieldImporter->field_info);
        $di=$currentFieldImporter->drupal_importer;
        $name=$fi["field_name"];
        $type=$fi["type"];
        //If ignored field return false
        $ignore_fields = apply_filters('drupal2wp_ignore_fields',self::$ignore_names);
        if(in_array($name,$ignore_fields)){
            return FALSE;
        }
        $fimp=$currentFieldImporter;
        switch($type){
            case "text_long":
            case "text":
            case "list_text":
                $fimp=new DrupalBaseField($fi,$di,array($name=>$name."_value"));
                break;
            case "image":
                if($fi["cardinality"]==1){
                    $fimp=new DrupalFileField($fi,$di);
                }else{
                    $fimp=new ACFGalleryField($fi,$di);
                }
                break;
            case "file":
                $fimp=new DrupalFileField($fi,$di);
                break;
            case "link_field":
                $fimp=new DrupalBaseField($fi,$di,array($name."_url"=>$name."_url",$name."_title"=>$name."_title"));
                break;
            case "taxonomy_term_reference":
                $fimp=FALSE;//Ignore field taxonomy_term_reference already handled by base import
                break;
            case "field_collection":
                $fimp=FALSE;//Ignore field collection
                break;
            case "youtube":
                $fimp=new DrupalBaseField($fi,$di,array($name."_video_url"=>$name."_input"));
                break;
            case "video_embed_field":
                $fimp=new DrupalBaseField($fi,$di,array($name."_video_url"=>$name."_video_url"));
                break;
            case "geolocation_latlng":
                $fimp=new ACFGoogleMapField($fi,$di);
                break;
            default:
                //text_with_summary
                //node_reference
                break;
        }
        
        return $fimp;
    }
    public function processMultiMeta($post_data,$field_data_array){
        $is_repeater=(int)$this->field_info["cardinality"]!=1;//Check if is a repeater field
        $this->post_data=$post_data;
        foreach($field_data_array AS $fd){
             $this->post_data = $this->processMeta($this->post_data,$fd,$is_repeater?$fd["delta"]:FALSE);
        }
        //Add ACF count field
        if($is_repeater && $this->repeater_post && $this->repeater_post_name ){
             $this->post_data["postmeta"][$this->repeater_post_name]=$this->repeater_post+1;
        }
        return $this->post_data;
    }
    
    public function processMeta($post_data,$field_data,$repeaterPos=FALSE){
        $this->post_data=$post_data;
        $this->field_data=$field_data;
        $this->repeater_post=$repeaterPos;
        $cfm=$this->generateCurrentFieldMap();
        foreach($cfm AS $k=>$c){
            $this->appendMeta($k, $c);
        }
        return $this->post_data;
    }
    
    public function appendMeta($wp_field_name,$drupal_field_name){
        $name=$this->field_info["field_name"];
        $this->post_data["postmeta"][$this->getFieldName($name, $wp_field_name)]=$this->getFieldValue($drupal_field_name);
    }
    
    
    public function generateCurrentFieldMap(){
        $this->current_field_map=array();
        if(empty($this->field_map)){
            //If empty field map take all fields except common ones
            foreach(array_keys($this->field_data) AS $c){
                if(!in_array($c,$this->ignore_cols)){
                    $this->field_map[$c]=$c;
                }
            }
        }else{
            foreach($this->field_map AS $k=>$v){
                $this->current_field_map[$k]=$v;
            }
        }
        return $this->current_field_map;
    }
    
    public function getFieldValue($drupal_field_name){
        return $this->field_data[$drupal_field_name];
    }
    
    public function getFieldName($name,$fname){
        $retname=$name;
        if(count($this->field_map)>1){
            //If fname starts with name ignore name
            if(strpos($fname, $name)===0){
                $retname=$fname;
            }else{
                $retname=$name."_".$fname;
            }
        }
        
        if($this->repeater_post!==FALSE){//ACF kind field name
            $field_part_name=$fname;
            if(strpos($field_part_name, $name."_")===0){
                $field_part_name= str_replace($name."_", "", $field_part_name);
            }
            $retname=$name."_".$this->repeater_post."_".$field_part_name;
            $this->repeater_post_name=str_replace("field_", "", $name);
            $this->repeater_post_name=apply_filters('drupal2wp_get_repeater_field_name',$this->repeater_post_name);
            $this->repeater_post_count+=1;
            
        }else{
            $this->repeater_post_name=FALSE;
            $this->repeater_post_count=0;
        }
        //Delete all field_ parts needed for ACF
        $retname=str_replace("field_", "", $retname);
        $nameData=array("final_name"=>$retname,"field_importer"=>$this,"name"=>$name,"fname"=>$fname);
        $nameData=apply_filters('drupal2wp_get_field_name_data',$nameData);
        return $nameData["final_name"];
    }
}

DrupalBaseField::registerBaseFields();
