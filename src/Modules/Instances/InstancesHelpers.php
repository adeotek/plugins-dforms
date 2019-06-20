<?php
/**
 * description
 *
 * @package    NETopes\Plugins\Modules\DForms
 * @author     George Benjamin-Schonberger
 * @copyright  Copyright (c) 2013 - 2019 AdeoTEK Software SRL
 * @license    LICENSE.md
 * @version    1.0.1.0
 * @filesource
 */
namespace NETopes\Plugins\DForms\Modules\Instances;
use NApp;
use NETopes\Core\App\Params;
use NETopes\Core\AppException;
use NETopes\Core\Controls\BasicForm;
use NETopes\Core\Controls\Button;
use NETopes\Core\Controls\ControlsHelpers;
use NETopes\Core\Controls\HiddenInput;
use NETopes\Core\Data\DataProvider;
use NETopes\Core\Data\VirtualEntity;
use NETopes\Core\Validators\Validator;
use Translate;

/**
 * Class InstancesHelpers
 *
 * @package NETopes\Plugins\DForms\Modules\Instances
 */
class InstancesHelpers {

    /**
     * description
     *
     * @param \NETopes\Core\Data\VirtualEntity $field
     * @param array                            $fParams
     * @param mixed                            $fValue
     * @param string|null                      $themeType
     * @param int                              $iCount
     * @return array
     * @throws \NETopes\Core\AppException
     */
    public static function PrepareRepeatableField(VirtualEntity $field,array $fParams,$fValue=NULL,?string $themeType=NULL,int $iCount=0): array {
        // NApp::Dlog(['$field'=>$field,'$fParams'=>$fParams,'$fValue'=>$fValue,'$themeType'=>$themeType,'$iCount'=>$iCount],'PrepareField');
        $idInstance=$field->getProperty('id_instance',NULL,'is_integer');
        $tagId=($idInstance ? $idInstance.'_' : '').$field->getProperty('uid','','is_string').'_'.$field->getProperty('name','','is_string');
        $fValuesArray=explode('|::|',$fValue);
        $field->set('tag_id',$tagId.'-0');
        $field->set('tag_name',$field->getProperty('id',NULL,'is_numeric').'[]');
        $field->set('value',get_array_value($fValuesArray,0,NULL,'isset'));
        $fClass=$field->getProperty('class','','is_string');
        $idValuesList=$field->getProperty('id_values_list',0,'is_numeric');
        if(in_array($fClass,['SmartComboBox','GroupCheckBox']) && $idValuesList>0) {
            $fParams['load_type']='database';
            $fParams['data_source']=[
                'ds_class'=>'Plugins\DForms\ValuesLists',
                'ds_method'=>'GetValues',
                'ds_params'=>['list_id'=>$idValuesList,'for_state'=>1],
            ];
        }//if(in_array($fClass,['SmartComboBox','GroupCheckBox']) && $idValuesList>0)
        $fParams=ControlsHelpers::ReplaceDynamicParams($fParams,$field,TRUE,'_dfp_');
        $fParams['container_class']='ctrl-repeatable';
        $removeAction=[
            [
                'type'=>'Button',
                'params'=>[
                    // 'tooltip'=>Translate::GetButton('remove_field'),
                    'icon'=>'fa fa-minus-circle',
                    'class'=>NApp::$theme->GetBtnDangerClass('pull-right clsRemoveRepeatableCtrlBtn'),
                    'onclick'=>"RemoveRepeatableControl(this)",
                ],
            ],
        ];
        $iCustomActions=[];
        for($i=1; $i<$iCount; $i++) {
            $tmpCtrl=$fParams;
            $tmpCtrl['container']='none';
            $tmpCtrl['no_label']=TRUE;
            $tmpCtrl['label_width']=NULL;
            $tmpCtrl['width']=NULL;
            $tmpCtrl['tag_id']=$tagId.'-'.$i;
            $tmpCtrl['value']=get_array_value($fValuesArray,$i,NULL,'isset');
            if(strpos($themeType,'bootstrap')!==FALSE) {
                $tmpCtrl['class'].=' form-control';
            }
            $tmpCtrl['extra_tag_params']=(isset($tmpCtrl['extra_tag_params']) && $tmpCtrl['extra_tag_params'] ? $tmpCtrl['extra_tag_params'].' ' : '').'data-tid="'.$tagId.'" data-ti="'.$i.'"';
            $tmpCtrl['actions']=$removeAction;
            $iCustomActions[]=[
                'type'=>$fClass,
                'params'=>$tmpCtrl,
            ];
        }//END for
        $iCustomActions[]=[
            'type'=>'Button',
            'params'=>[
                'value'=>Translate::GetButton('add_field'),
                'icon'=>'fa fa-plus',
                'class'=>'add-ctrl-btn clsAddRepeatableCtrlBtn',
                'onclick'=>"AddRepeatableControl(this,'{$tagId}')",
                'extra_tag_params'=>'data-ract="&nbsp;'.Translate::GetButton('remove_field').'"',
            ],
        ];
        $fParams['extra_tag_params']=(isset($fParams['extra_tag_params']) && $fParams['extra_tag_params'] ? $fParams['extra_tag_params'].' ' : '').'data-tid="'.$tagId.'" data-ti="0"';
        $fParams['actions']=$removeAction;
        $fParams['custom_actions']=$iCustomActions;
        // NApp::Dlog($fClass,'$fClass');
        // NApp::Dlog($fParams,'$fParams');
        $colSpan=$field->getProperty('colspan',0,'is_integer');
        if($colSpan>1) {
            return [
                'colspan'=>$colSpan,
                'control_type'=>$fClass,
                'control_params'=>$fParams,
            ];
        }//if($colSpan>1)
        return [
            'control_type'=>$fClass,
            'control_params'=>$fParams,
        ];
    }//END public static function PrepareRepeatableField

    /**
     * description
     *
     * @param \NETopes\Core\Data\VirtualEntity $field
     * @param array                            $fParams
     * @param mixed                            $fValue
     * @param string|null                      $themeType
     * @param bool                             $repeatable
     * @param int                              $iCount
     * @return array
     * @throws \NETopes\Core\AppException
     */
    public static function PrepareField(VirtualEntity $field,array $fParams,$fValue=NULL,?string $themeType=NULL,bool $repeatable=FALSE,int $iCount=0): array {
        // NApp::Dlog(['$field'=>$field,'$fParams'=>$fParams,'$fValue'=>$fValue,'$themeType'=>$themeType,'$iCount'=>$iCount],'PrepareField');
        if($repeatable) {
            return static::PrepareRepeatableField($field,$fParams,$fValue,$themeType,$iCount);
        }
        $idInstance=$field->getProperty('id_instance',NULL,'is_integer');
        $tagId=($idInstance ? $idInstance.'_' : '').$field->getProperty('uid','','is_string').'_'.$field->getProperty('name','','is_string');
        $field->set('tag_id',$tagId);
        $field->set('tag_name',$field->getProperty('id',NULL,'is_numeric'));
        $field->set('value',$fValue);
        // if(strlen($themeType)) { $fParams['theme_type'] = $themeType; }
        $fClass=$field->getProperty('class','','is_string');
        if($fClass=='Message') {
            $flabel=$field->getProperty('label','','is_string');
            $fdesc=$field->getProperty('description','','is_string');
            $fParams['text']=$flabel.$fdesc;
        }//if($fClass=='Message')
        $idValuesList=$field->getProperty('id_values_list',0,'is_numeric');
        if(in_array($fClass,['SmartComboBox','GroupCheckBox']) && $idValuesList>0) {
            $fParams['load_type']='database';
            $fParams['data_source']=[
                'ds_class'=>'Plugins\DForms\ValuesLists',
                'ds_method'=>'GetValues',
                'ds_params'=>['list_id'=>$idValuesList,'for_state'=>1],
            ];
        }//if(in_array($fClass,['SmartComboBox','GroupCheckBox']) && $idValuesList>0)
        $fParams=ControlsHelpers::ReplaceDynamicParams($fParams,$field,TRUE,'_dfp_');
        $colSpan=$field->getProperty('colspan',0,'is_integer');
        if($colSpan>1) {
            return [
                'colspan'=>$colSpan,
                'control_type'=>$fClass,
                'control_params'=>$fParams,
            ];
        }//if($colSpan>1)
        return [
            'control_type'=>$fClass,
            'control_params'=>$fParams,
        ];
    }//END public static function PrepareField

    /**
     * Prepare add/edit form/sub-form page
     *
     * @param \NETopes\Core\App\Params|null    $params Parameters object
     * @param \NETopes\Core\Data\VirtualEntity $template
     * @param \NETopes\Core\Data\VirtualEntity $page
     * @param bool                             $multiPage
     * @param string                           $tName
     * @param int|null                         $idInstance
     * @param int|null                         $idSubForm
     * @param int|null                         $idItem
     * @param int|null                         $index
     * @return array Returns BasicForm configuration array
     * @throws \NETopes\Core\AppException
     */
    public static function PrepareFormPage(?Params $params,VirtualEntity $template,VirtualEntity $page,string $tName,bool $multiPage=FALSE,?int $idInstance=NULL,?int $idSubForm=NULL,?int $idItem=NULL,?int $index=NULL): ?array {
        // NApp::Dlog(['$template'=>$template,'$page'=>$page,'$tName'=>$tName,'$multiPage'=>$multiPage,'$idInstance'=>$idInstance,'$idSubForm'=>$idSubForm,'$idItem'=>$idItem,'$index'=>$index],'PrepareFormPage');
        if($idSubForm) {
            $fields=DataProvider::Get('Plugins\DForms\Instances','GetStructure',[
                'template_id'=>$template->getProperty('id'),
                'instance_id'=>($idInstance ? $idInstance : NULL),
                'for_pindex'=>$page->getProperty('pindex',-1,'is_integer'),
                'item_id'=>$idItem,
                'for_index'=>(is_numeric($index) ? $index : NULL),
            ]);
        } else {
            $fields=DataProvider::Get('Plugins\DForms\Instances','GetStructure',[
                'template_id'=>$template->getProperty('id'),
                'instance_id'=>($idInstance ? $idInstance : NULL),
                'for_pindex'=>$page->getProperty('pindex',-1,'is_integer'),
            ]);
        }//if($idSubForm)
        // NApp::Dlog($fields,'$fields');
        $themeType=$template->getProperty('theme_type','','is_string');
        $controlsSize=$template->getProperty('controls_size','','is_string');
        // $separatorWidth=$template->getProperty('separator_width','','is_string');
        $labelCols=$template->getProperty('label_cols',NULL,'is_not0_integer');

        $ctrl_params=NULL;
        $pIndex=$page->getProperty('pindex');
        $colsNo=$page->getProperty('colsno');
        $iPrefix=($idInstance ? $idInstance.'_' : '');
        $columnsToSkip=0;
        $formContent=[];
        /** @var VirtualEntity $field */
        foreach($fields as $field) {
            $row=$field->getProperty('frow',0,'is_numeric');
            if(!$row) {
                continue;
            }
            if(!isset($formContent[$row])) {
                $formContent[$row]=[];
                $columnsToSkip=0;
            }//if(!isset($formContent[$row]))
            $fClass=$field->getProperty('class','','is_string');
            // if($idSubForm) { NApp::Dlog($field,$fClass); }
            $fParamsStr=$field->getProperty('params','','is_string');
            $fParams=strlen($fParamsStr) ? json_decode($fParamsStr,TRUE) : [];
            switch($fClass) {
                case 'FormTitle':
                    $formContent[$row]=[
                        'separator'=>'title',
                        'value'=>$field->getProperty('label','','is_string'),
                        'class'=>get_array_value($fParams,'class','','is_string'),
                    ];
                    $columnsToSkip=$colsNo - 1;
                    break;
                case 'FormSubTitle':
                    $formContent[$row]=[
                        'separator'=>'subtitle',
                        'value'=>$field->getProperty('label','','is_string'),
                        'class'=>get_array_value($fParams,'class','','is_string'),
                    ];
                    $columnsToSkip=$colsNo - 1;
                    break;
                case 'FormSeparator':
                    $formContent[$row]=['separator'=>'separator'];
                    $columnsToSkip=$colsNo - 1;
                    break;
                case 'BasicForm':
                    $colSpan=$field->getProperty('colspan',0,'is_integer');
                    if($colSpan>1) {
                        $columnsToSkip=$colSpan - 1;
                    }
                    $fParams=['value'=>''];
                    $tagId=$iPrefix.$field->getProperty('cell','','is_string').'_'.$field->getProperty('name','','is_string').($index ? '_'.$index : '');
                    $fIType=$field->getProperty('itype',1,'is_not0_numeric');
                    $fIdSubForm=$field->getProperty('id_sub_form',-1,'is_not0_numeric');
                    $idItem=$field->getProperty('id',NULL,'is_not0_numeric');
                    if($fIType==2 && $idInstance) {
                        $fICount=$field->getProperty('icount',1,'is_not0_numeric');
                        // $fValue = $field->getProperty('ivalues',NULL,'is_string');
                    } else {
                        $fICount=1;
                        // $fValue = NULL;
                    }//if($fIType==2 && $idInstance)
                    for($i=0; $i<$fICount; $i++) {
                        $ctrl_params=static::PrepareForm($params,$template,$idInstance,$fIdSubForm,$idItem,$i);
                        if(!$ctrl_params) {
                            throw new AppException('Invalid DynamicForm sub-form configuration!');
                        }
                        $ctrl_params['sub_form_tag_id']=$tagId.'-'.$i;
                        if($fIType==2) {
                            $ctrl_params['tags_ids_sufix']='-'.$i;
                            $ctrl_params['tags_names_sufix']='[]';
                            $ctrl_params['sub_form_class']='clsRepeatableField';
                            $ctrl_params['sub_form_extra_tag_params']='data-tid="'.$tagId.'" data-ti="'.$i.'"';
                        }//if($fIType==2)
                        // NApp::Dlog($ctrl_params,'$ctrl_params');
                        $basicForm=new BasicForm($ctrl_params);
                        $fParams['value'].=$basicForm->Show();
                        // NApp::Dlog($fParams['value'],'fcontent');
                        if($i>0) {
                            $ctrl_ract=new Button(['value'=>Translate::GetButton('remove_field'),'icon'=>'fa fa-minus-circle','class'=>NApp::$theme->GetBtnWarningClass('clsRepeatableCtrlBtn'),'clear_base_class'=>TRUE,'onclick'=>"RemoveRepeatableForm(this,'{$tagId}-{$i}')"]);
                            $fParams['value'].=$ctrl_ract->Show();
                        }//if($i>0)
                    }//END for
                    if($fIType==2) {
                        $ctrl_ract=new Button(['value'=>Translate::GetButton('add_element'),'icon'=>'fa fa-plus-circle','class'=>NApp::$theme->GetBtnDefaultClass('clsRepeatCtrlBtn'),'onclick'=>"RepeatForm(this,'{$tagId}')",'extra_tag_params'=>'data-ract="'.Translate::GetButton('remove_element').'" data-ract-class="'.NApp::$theme->GetBtnWarningClass('clsRepeatableCtrlBtn').'"']);
                        $fParams['value'].=$ctrl_ract->Show();
                    }//if($fIType==2)
                    $formContent[$row][]=[
                        'width'=>$field->getProperty('width','','is_string'),
                        'control_type'=>'CustomControl',
                        'control_params'=>$fParams,
                    ];
                    break;
                default:
                    if(!is_array($fParams) || !count($fParams)) {
                        if($columnsToSkip>0) {
                            $columnsToSkip--;
                            continue 2;
                        }//if($columnsToSkip>0)
                        $formContent[$row][]=[];
                    } else {
                        $colSpan=$field->getProperty('colspan',0,'is_integer');
                        if($colSpan>1) {
                            $columnsToSkip=$colSpan - 1;
                        }
                        $fIType=$field->getProperty('itype',1,'is_not0_numeric');
                        if($fIType==2) {
                            if($idInstance) {
                                $fICount=$field->getProperty('icount',0,'is_numeric');
                                $fValue=$field->getProperty('ivalues',NULL,'is_string');
                            } else {
                                $fICount=0;
                                $fValue=NULL;
                            }//if($idInstance)
                            $formContent[$row][]=static::PrepareField($field,$fParams,$fValue,$themeType,TRUE,$fICount);
                        } else {
                            $fValue=NULL;
                            if($idInstance) {
                                $fValue=$field->getProperty('ivalues',NULL,'is_string');
                            }
                            $formContent[$row][]=static::PrepareField($field,$fParams,$fValue,$themeType);
                        }//if($fIType==2)
                    }//if(!is_array($fParams) || !count($fParams))
                    break;
            }//END switch
        }//END foreach

        if($multiPage) {
            $ctrl_params=[
                'type'=>'fixed',
                'uid'=>$tName.'-'.$pIndex,
                'name'=>$page->getProperty('tr_title'),
                'content_type'=>'control',
                'content'=>[
                    'control_type'=>'BasicForm',
                    'control_params'=>[
                        'tag_id'=>'df_'.$tName.'_'.$pIndex.'_form',
                        'response_target'=>'df_'.$tName.'_'.$pIndex.'_errors',
                        'cols_no'=>$colsNo,
                    ],
                ],
            ];
            if(strlen($themeType)) {
                $ctrl_params['content']['control_params']['theme_type']=$themeType;
            }
            if(is_numeric($labelCols) && $labelCols>=1 && $labelCols<=12) {
                $ctrl_params['content']['control_params']['label_cols']=$labelCols;
            }
            if(strlen($controlsSize)) {
                $ctrl_params['content']['control_params']['controls_size']=$controlsSize;
            }
            $ctrl_params['content']['control_params']['content']=$formContent;
        } else {
            $formId='df_'.$tName.'_form';
            $ctrl_params=[
                'control_class'=>'BasicForm',
                'tname'=>$tName,
                'tag_id'=>$formId,
                'cols_no'=>$colsNo,
            ];
            if(!$idSubForm) {
                $ctrl_params['response_target']='df_'.$tName.'_errors';
            }
            if(strlen($themeType)) {
                $ctrl_params['theme_type']=$themeType;
            }
            if(is_numeric($labelCols) && $labelCols>=1 && $labelCols<=12) {
                $ctrl_params['label_cols']=$labelCols;
            }
            if(strlen($controlsSize)) {
                $ctrl_params['controls_size']=$controlsSize;
            }
            $ctrl_params['content']=$formContent;
        }//if($multiPage)
        return $ctrl_params;
    }//END public static function PrepareFormPage

    /**
     * Prepare add/edit form/sub-form
     *
     * @param \NETopes\Core\App\Params|null $params Parameters object
     * @param VirtualEntity|null            $mTemplate
     * @param int|null                      $idInstance
     * @param int|null                      $idSubForm
     * @param int|null                      $idItem
     * @param int|null                      $index
     * @return array Returns BasicForm configuration array
     * @throws \NETopes\Core\AppException
     */
    public static function PrepareForm(?Params $params,?VirtualEntity $mTemplate,?int $idInstance=NULL,?int $idSubForm=NULL,?int $idItem=NULL,?int $index=NULL): ?array {
        // NApp::Dlog(['$mTemplate'=>$mTemplate,'$idInstance'=>$idInstance,'$idSubForm'=>$idSubForm,'$idItem'=>$idItem,'$index'=>$index],'PrepareForm');
        $idTemplate=$mTemplate->getProperty('id',NULL,'is_integer');
        if(!$idTemplate) {
            return NULL;
        }
        if($idSubForm) {
            $template=DataProvider::Get('Plugins\DForms\Instances','GetTemplate',[
                'for_id'=>$idSubForm,
                'for_code'=>NULL,
                'instance_id'=>($idInstance ? $idInstance : NULL),
                'for_state'=>1,
            ]);
            $idSubForm=$template->getProperty('id',NULL,'is_integer');
            // NApp::Dlog($idItem,'$idItem');
            // NApp::Dlog($idSubForm,'$idSubForm');
            // NApp::Dlog($template,'$template');
            if(!$idSubForm || !$idItem) {
                return NULL;
            }
            $pages=DataProvider::Get('Plugins\DForms\Instances','GetPages',[
                'for_id'=>NULL,
                'instance_id'=>($idInstance ? $idInstance : NULL),
                'template_id'=>$idTemplate,
                'for_template_code'=>NULL,
                'for_pindex'=>NULL,
            ]);
        } else {
            $template=$mTemplate;
            $pages=DataProvider::Get('Plugins\DForms\Instances','GetPages',[
                'for_id'=>NULL,
                'instance_id'=>($idInstance ? $idInstance : NULL),
                'template_id'=>$idTemplate,
                'for_template_code'=>NULL,
                'for_pindex'=>NULL,
            ]);
            // NApp::Dlog($relations,'$relations');
        }//if($idSubForm)
        // NApp::Dlog($pages,'$pages');
        if(!is_iterable($pages) || !count($pages)) {
            return NULL;
        }
        $iPrefix=($idInstance ? $idInstance.'_' : '');
        $tName=$iPrefix.$idTemplate.'_'.$idSubForm;
        $renderType=get_array_value($template,'render_type',1,'is_integer');
        if(in_array($renderType,[21,22])) {
            $ctrl_params=[
                'control_class'=>'TabControl',
                'tname'=>$tName,
                'tag_id'=>'df_'.$tName.'_form',
                'mode'=>($renderType==22 ? 'accordion' : 'tabs'),
                'tabs'=>[],
            ];
            foreach($pages as $page) {
                $ctrl_params['tabs'][]=static::PrepareFormPage($params,$template,$page,$tName,TRUE,$idInstance,$idSubForm,$idItem,$index);
            }//END foreach
        } else {
            $ctrl_params=static::PrepareFormPage($params,$template,$pages->first(),$tName,FALSE,$idInstance,$idSubForm,$idItem,$index);
        }//if(in_array($renderType,[21,22]))
        return $ctrl_params;
    }//END public static function PrepareForm

    /**
     * @param int                      $idTemplate
     * @param int|null                 $idInstance
     * @param \NETopes\Core\App\Params $inputParams
     * @return string|null
     * @throws \NETopes\Core\AppException
     */
    public static function PrepareRelationsFormPart(int $idTemplate,?int $idInstance,Params $inputParams): ?string {
        if($idInstance) {
            $relations=DataProvider::Get('Plugins\DForms\Instances','GetRelations',['instance_id'=>$idInstance]);
        } else {
            $relations=DataProvider::Get('Plugins\DForms\Templates','GetRelations',['template_id'=>$idTemplate]);
        }//if($idInstance)
        $result=NULL;
        if(is_iterable($relations) && count($relations)) {
            /** @var VirtualEntity $rel */
            foreach($relations as $rel) {
                if($rel->getProperty('rtype')!=30) {
                    continue;
                }
                //Programmatically (input parameter)
                $rValue=$inputParams->safeGet($rel->getProperty('key'),NULL,'?isset');
                if(is_null($rValue)) {
                    continue;
                }
                $rctrl=new HiddenInput(['tag_id'=>$rel->getProperty('key'),'postable'=>TRUE,'value'=>$rValue]);
                $result.=$rctrl->Show();
            }//END foreach
        }//if(is_iterable($relations) && count($relations))
        return $result;
    }//END public static function PrepareRelationsFormPart

    /**
     * @param mixed       $value
     * @param mixed|null  $returnValue
     * @param string|null $dataType
     * @param string|null $key
     * @return bool
     * @throws \NETopes\Core\AppException
     */
    public static function GetValidatedValue($value,&$returnValue=NULL,?string $dataType=NULL,string $key=NULL): bool {
        if(is_array($value) && strlen($key)) {
            $lValue=get_array_param($value,$key,NULL,'isset');
        } else {
            $lValue=$value;
        }//if(is_array($value) && strlen($key))
        switch($dataType) {
            case 'integer':
                $returnValue=Validator::ValidateValue($lValue,NULL,'?is_integer');
                return !is_integer($returnValue);
            case 'numeric':
                $returnValue=Validator::ValidateValue($lValue,NULL,'?is_numeric');
                return !is_numeric($returnValue);
            case 'string':
            default:
                $returnValue=Validator::ValidateValue($lValue,NULL,'?is_string');
                return !strlen($returnValue);
        }//END switch
    }//END public static function GetValidatedValue

    /**
     * @param int                      $idTemplate
     * @param int|null                 $idInstance
     * @param \NETopes\Core\App\Params $params
     * @return bool
     * @throws \NETopes\Core\AppException
     */
    public static function ValidateRelations(int $idTemplate,?int $idInstance,Params &$params): bool {
        if($idInstance) {
            $relations=DataProvider::Get('Plugins\DForms\Instances','GetRelations',['instance_id'=>$idInstance]);
        } else {
            $relations=DataProvider::Get('Plugins\DForms\Templates','GetRelations',['template_id'=>$idTemplate]);
        }//if($idInstance)
        // NApp::Dlog($relations,'$relations');
        $relationsData=$params->safeGet('relations',[],'is_array');
        $fieldsData=$params->safeGet('data',[],'is_array');
        $errors=[];
        /** @var VirtualEntity $rel */
        foreach($relations as $k=>$rel) {
            $relKey=$rel->getProperty('key',NULL,'is_string');
            $dType=$rel->getProperty('dtype','','is_string');
            $relValue=NULL;
            switch($rel->getProperty('rtype',0,'is_integer')) {
                case 10: // USER INPUT (AS FORM ELEMENT)
                    static::GetValidatedValue($fieldsData,$relValue,$dType,$relKey);
                    break;
                case 20: // AUTO(FROM SESSION)
                    static::GetValidatedValue(NApp::GetParam($relKey),$relValue,$dType);
                    break;
                case 21: // AUTO(FROM PAGE SESSION)
                    static::GetValidatedValue(NApp::GetPageParam($relKey),$relValue,$dType);
                    break;
                case 30: // PROGRAMMATICALLY (METHOD INPUT PARAMETER)
                    static::GetValidatedValue($relationsData,$relValue,$dType,$relKey);
                    break;
            }//END switch
            if($rel->getProperty('required',FALSE,'bool') && !$relValue) {
                $errors[]=[
                    'id'=>$rel->getProperty('id',NULL,'is_integer'),
                    'key'=>$relKey,
                    'name'=>$rel->getProperty('name','','is_string'),
                    'type'=>'required_relation',
                ];
                continue;
            }
            $relations->set($k,$rel->set('ivalue',($dType=='integer' ? $relValue : NULL)));
            $relations->set($k,$rel->set('svalue',($dType!='integer' ? $relValue : NULL)));
        }//END foreach
        $params->set('df_relations_values',$relations);
        $params->set('df_relations_errors',$errors);
        return !count($errors);
    }//END public static function ValidateRelations

    /**
     * @param int                      $idTemplate
     * @param int|null                 $idInstance
     * @param \NETopes\Core\App\Params $params
     * @return bool
     * @throws \NETopes\Core\AppException
     */
    public static function ValidateFields(int $idTemplate,?int $idInstance,Params &$params): bool {
        $fieldsData=$params->safeGet('data',[],'is_array');
        $fields=DataProvider::Get('Plugins\DForms\Instances','GetFields',['template_id'=>$idTemplate]);
        // NApp::Dlog($fields,'$fields');
        $errors=[];
        /** @var VirtualEntity $field */
        foreach($fields as $k=>$field) {
            $fieldId=$field->getProperty('id',NULL,'is_integer');
            $fieldName=$field->getProperty('name','N/A','is_string');
            if(!$fieldId) {
                $errors[]=[
                    'name'=>$fieldName,
                    'label'=>$field->getProperty('label','','is_string'),
                    'type'=>'invalid_id',
                ];
                continue;
            }
            if($field->getProperty('itype',0,'is_integer')==2 || $field->getProperty('parent_itype',0,'is_integer')==2) {
                $fieldsValues=get_array_value($fieldsData,$fieldId,NULL,'is_array');
                if(!is_array($fieldsValues) || !count($fieldsValues)) {
                    if($field->getProperty('required',FALSE,'bool')) {
                        $errors[]=[
                            'id'=>$fieldId,
                            'name'=>$fieldName,
                            'label'=>$field->getProperty('label','','is_string'),
                            'type'=>'required_field',
                        ];
                        continue;
                    }
                    $fields->set($k,$field->set('value',NULL));
                } else {
                    $fieldValue=[];
                    foreach($fieldsValues as $i=>$fv) {
                        switch($field->getProperty('data_type',NULL,'is_string')) {
                            case 'numeric':
                                $fieldItemValue=Validator::ValidateValue($fv,NULL,'is_numeric');
                                if($field->getProperty('required',FALSE,'bool') && !is_numeric($fieldItemValue)) {
                                    $errors[]=[
                                        'id'=>$fieldId,
                                        'name'=>$fieldName,
                                        'label'=>$field->getProperty('label','','is_string'),
                                        'type'=>'required_field',
                                        'index'=>$i,
                                    ];
                                    continue 2;
                                }
                                $fieldValue[$i]=$fieldItemValue;
                                break;
                            case 'string':
                            default:
                                $fieldItemValue=Validator::ValidateValue($fv,NULL,'is_string');
                                if($field->getProperty('required',FALSE,'bool') && !strlen($fieldItemValue)) {
                                    $errors[]=[
                                        'id'=>$fieldId,
                                        'name'=>$fieldName,
                                        'label'=>$field->getProperty('label','','is_string'),
                                        'type'=>'required_field',
                                        'index'=>$i,
                                    ];
                                    continue 2;
                                }
                                $fieldValue[$i]=$fieldItemValue;
                                break;
                        }//END switch
                    }//END foreach
                    if(!count($errors)) {
                        $fields->set($k,$field->set('value',$fieldValue));
                    }
                }//if(!is_array($fieldsValues) || !count($fieldsValues))
            } else {
                switch($field->getProperty('data_type',NULL,'is_string')) {
                    case 'numeric':
                        $fieldValue=get_array_value($fieldsData,$fieldId,NULL,'is_numeric');
                        if($field->getProperty('required',FALSE,'bool') && !is_numeric($fieldValue)) {
                            $errors[]=[
                                'id'=>$fieldId,
                                'name'=>$fieldName,
                                'label'=>$field->getProperty('label','','is_string'),
                                'type'=>'required_field',
                            ];
                            continue 2;
                        }
                        $fields->set($k,$field->set('value',$fieldValue));
                        break;
                    case 'string':
                    default:
                        $fieldValue=get_array_value($fieldsData,$fieldId,NULL,'is_string');
                        if($field->getProperty('required',FALSE,'bool') && !strlen($fieldValue)) {
                            $errors[]=[
                                'id'=>$fieldId,
                                'name'=>$fieldName,
                                'label'=>$field->getProperty('label','','is_string'),
                                'type'=>'required_field',
                            ];
                            continue 2;
                        }
                        $fields->set($k,$field->set('value',$fieldValue));
                        break;
                }//END switch
            }//if($field->getProperty('itype',0,'is_integer')==2 || $field->getProperty('parent_itype',0,'is_integer')==2)
        }//END foreach
        $params->set('df_fields_values',$fields);
        $params->set('df_fields_errors',$errors);
        return !count($errors);
    }//END public static function ValidateFields

    /**
     * @param \NETopes\Core\App\Params $params
     * @return bool
     * @throws \NETopes\Core\AppException
     */
    public static function ValidateSaveParams(Params &$params): bool {
        $idTemplate=$params->getOrFail('id_template','is_not0_integer','Invalid template identifier!');
        $idInstance=$params->safeGet('id',NULL,'?is_integer');
        if(!static::ValidateRelations($idTemplate,$idInstance,$params)) {
            return FALSE;
        }
        if(!static::ValidateFields($idTemplate,$idInstance,$params)) {
            return FALSE;
        }
        return TRUE;
    }//END public static function ValidateSaveParams
}//END class InstancesHelpers