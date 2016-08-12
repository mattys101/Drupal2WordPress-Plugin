<?php
/**
 * ACFGalleryField
 *
 * @author codeko
 */
class ACFGalleryField extends DrupalFileField{
    public function appendMeta($wp_field_name,$drupal_field_name){
        $name=$this->field_info["field_name"];
        $post_id=$this->post_data["post_id"];
        if(!isset(self::$media_post_data[$post_id])){
            self::$media_post_data[$post_id]=array();
        }
        $file_id=$this->getFieldValue($name."_fid");
        $post_field=$this->getFieldName($name, $wp_field_name);
        //Not add to current post meta. Add to defered meta
        self::$media_post_data[$post_id][$post_field]["gallery"]=true;
        self::$media_post_data[$post_id][$post_field]["post_field"]=$post_field;
        self::$media_post_data[$post_id][$post_field]["images"][]=array("file_id"=>$file_id,"post_type"=>$this->post_data["post_type"],"post_field"=>$post_field,"post_id"=>$post_id, "alt"=>$this->getFieldValue($name."_alt"),"title"=>$this->getFieldValue($name."_title"));
    }
    
    
    public function getFieldName($name,$fname){
        //Delete all field_ parts needed for ACF
        $retname=str_replace("field_", "", $name);
        $nameData=array("final_name"=>$retname,"field_importer"=>$this,"name"=>$name,"fname"=>$fname);
        $nameData=apply_filters('drupal2wp_get_field_name_data',$nameData);
        return $nameData["final_name"];
    }
}
