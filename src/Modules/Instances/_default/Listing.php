<?php
use NETopes\Core\App\Module;
use NETopes\Core\Controls\TableViewBuilder;

/** @var string $target */
/** @var string $cModule */
/** @var string $cMethod */
/** @var string $cTarget */
/** @var string $listingTarget */
/** @var bool $inListingActions */
/** @var array $listingAddAction */
/** @var \NETopes\Core\Data\DataSet $fields */
/** @var \NETopes\Core\Data\DataSet|null $fTypes */
$ctrl_builder=new TableViewBuilder([
    'module'=>$cModule,
    'method'=>$cMethod,
    'persistent_state'=>FALSE,
    'target'=>$listingTarget,
    'alternate_row_color'=>TRUE,
    'scrollable'=>FALSE,
    'with_filter'=>TRUE,
    'with_pagination'=>TRUE,
    'sortby'=>['column'=>'CREATE_DATE','direction'=>'ASC'],
    'qsearch'=>'for_text',
    'ds_class'=>'Plugins\DForms\Instances',
    'ds_method'=>'GetInstances',
    'ds_params'=>['for_id'=>NULL,'template_id'=>$this->idTemplate,'for_template_code'=>NULL,'for_state'=>NULL,'for_text'=>NULL],
    'auto_load_data'=>TRUE,
]);
if($inListingActions) {
    $ctrl_builder->AddCustomAction([
        'control_type'=>'Button',
        'dright'=>Module::DRIGHT_ADD,
        'control_params'=>$listingAddAction,
    ]);
}
$ctrl_builder->AddAction('actions',[
    'dright'=>Module::DRIGHT_PRINT,
    'type'=>'Link',
    'params'=>['tooltip'=>Translate::GetButton('pdf'),'class'=>NApp::$theme->GetBtnSuccessClass('btn-xxs'),'icon'=>'fa fa-file-pdf-o',
        'href'=>NApp::$appBaseUrl.'/pipe/cdn.php',
        'target'=>'_blank',
        'url_params'=>[
            'namespace'=>NApp::$currentNamespace,
            'language'=>NApp::GetLanguageCode(),
            'rtype'=>'shash',
            'mrt'=>'1',
            // 'dbg'=>1,
        ],
        'session_params'=>[
            'module'=>$this->class,
            'method'=>'GetInstancePdf',
            'params'=>['id'=>'{!id!}','result_type'=>1,'cache'=>TRUE],
        ],
    ],
]);
$ctrl_builder->AddAction('actions',[
    'dright'=>Module::DRIGHT_EDIT,
    'type'=>'DivButton',
    'ajax_command'=>"{ 'module': '{$this->class}', 'method': 'ShowEditForm', 'params': { 'id': {!id!}, 'id_template': '{!id_template!}', 'c_module': '{$cModule}', 'c_method': '{$cMethod}', 'c_target': '{$cTarget}' } }",
    'ajax_target_id'=>$target,
    'params'=>['tag_id'=>'df_instance_edit_btn','tooltip'=>Translate::GetButton('edit'),'class'=>NApp::$theme->GetBtnPrimaryClass('btn-xxs'),'icon'=>'fa fa-pencil-square-o'],
]);
$ctrl_builder->AddAction('actions',[
    'dright'=>Module::DRIGHT_VIEW,
    'type'=>'DivButton',
    'ajax_command'=>"{ 'module': '{$this->class}', 'method': 'ShowViewForm', 'params': { 'id': {!id!}, 'id_template': '{!id_template!}', 'is_modal': 1 } }",
    'ajax_target_id'=>'modal',
    'params'=>['tag_id'=>'df_template_view_btn','tooltip'=>Translate::GetButton('view'),'class'=>NApp::$theme->GetBtnInfoClass('btn-xxs pull-right'),'icon'=>'fa fa-eye'],
]);

if(is_iterable($fields) && count($fields)) {
    /** @var \NETopes\Core\Data\IEntity $field */
    foreach($fields as $field) {
        if($field->getProperty('listing',0,'is_integer')!=1) {
            continue;
        }
        $fName=$field->getProperty('name','','is_string');
        switch($field->getProperty('class','','is_string')) {
            case 'CheckBox':
                $fDataType='numeric';
                $fType='checkbox';
                $fLabel=$field->getProperty('label','','is_string');
                break;
            case 'SmartComboBox':
            default:
                $fDataType='string';
                $fType='value';
                $fLabel=$field->getProperty('label','','is_string');
                break;
        }//END switch
        $ctrl_builder->SetColumn('item-'.$fName,[
            'db_field'=>'item-'.$fName,
            'data_type'=>$fDataType,
            'type'=>$fType,
            'halign'=>'left',
            'default_value'=>'-',
            'label'=>$fLabel,
        ]);
    }//END foreach
}//if(is_iterable($fields) && count ($fields))

if(is_array($this->showInListing)) {
    foreach($this->showInListing as $fName) {
        switch($fName) {
            case 'template_code':
                $ctrl_builder->SetColumn('template_code',[
                    'db_field'=>'template_code',
                    'data_type'=>'numeric',
                    'type'=>'value',
                    'format'=>'integer',
                    'halign'=>'center',
                    'label'=>Translate::GetLabel('template_code'),
                    'sortable'=>TRUE,
                    'filterable'=>TRUE,
                ]);
                break;
            case 'template_name':
                $ctrl_builder->SetColumn('template_name',[
                    'db_field'=>'template_name',
                    'data_type'=>'string',
                    'type'=>'value',
                    'halign'=>'left',
                    'label'=>Translate::GetLabel('template_name'),
                    'sortable'=>TRUE,
                    'filterable'=>TRUE,
                ]);
                break;
            case 'version':
                $ctrl_builder->SetColumn('version',[
                    'db_field'=>'version',
                    'data_type'=>'numeric',
                    'type'=>'value',
                    'format'=>'integer',
                    'halign'=>'center',
                    'label'=>Translate::GetLabel('version'),
                    'sortable'=>TRUE,
                    'filterable'=>TRUE,
                ]);
                break;
            case 'ftype':
                $ctrl_builder->SetColumn('ftype',[
                    'db_field'=>'ftype',
                    'data_type'=>'numeric',
                    'type'=>'indexof',
                    'values_collection'=>$fTypes,
                    'halign'=>'center',
                    'label'=>Translate::GetLabel('type'),
                    'sortable'=>TRUE,
                    'filterable'=>TRUE,
                    'filter_type'=>'combobox',
                    'show_filter_cond_type'=>FALSE,
                    'filter_params'=>['value_field'=>'id','display_field'=>'name','selected_value'=>NULL],
                    'filter_data_source'=>[
                        'ds_class'=>'_Custom\DFormsOffline',
                        'ds_method'=>'GetDynamicFormsTemplatesFTypes',
                    ],
                ]);
                break;
            case 'iso_code':
                $ctrl_builder->SetColumn('iso_code',[
                    'db_field'=>'iso_code',
                    'data_type'=>'string',
                    'type'=>'value',
                    'halign'=>'center',
                    'default_value'=>'-',
                    'label'=>Translate::GetLabel('iso_code'),
                    'sortable'=>TRUE,
                    'filterable'=>TRUE,
                ]);
                break;
            case 'state':
                $ctrl_builder->SetColumn('state',[
                    'width'=>'60',
                    'db_field'=>'state',
                    'data_type'=>'numeric',
                    'type'=>'control',
                    'control_type'=>'JqCheckBox',
                    'control_params'=>['container'=>FALSE,'no_label'=>TRUE,'tag_id'=>'df_instance_update_state','jqparams'=>'{ type: 5 }','onchange'=>"AjaxRequest('{$cModule}','EditRecordState','id'|'{!id!}'~'state'|df_instance_update_state_{!id!}:value)->errors"],
                    'control_pafreq'=>['onchange'],
                    'label'=>Translate::GetLabel('active'),
                    'sortable'=>TRUE,
                    'filterable'=>TRUE,
                    'filter_type'=>'combobox',
                    'show_filter_cond_type'=>FALSE,
                    'filter_params'=>['value_field'=>'id','display_field'=>'name','selected_value'=>NULL],
                    'filter_data_source'=>[
                        'ds_class'=>'_Custom\DFormsOffline',
                        'ds_method'=>'GetGenericArrays',
                        'ds_params'=>['type'=>'active'],
                    ],
                ]);
                break;
            case 'create_date':
                $ctrl_builder->SetColumn('create_date',[
                    'width'=>'120',
                    'db_field'=>'create_date',
                    'data_type'=>'datetime',
                    'type'=>'value',
                    'halign'=>'center',
                    'label'=>Translate::GetLabel('created_at'),
                    'sortable'=>TRUE,
                    'filterable'=>FALSE,
                ]);
                break;
            case 'user_full_name':
                $ctrl_builder->SetColumn('user_full_name',[
                    'db_field'=>'user_full_name',
                    'data_type'=>'string',
                    'type'=>'value',
                    'halign'=>'center',
                    'label'=>Translate::GetLabel('created_by'),
                    'sortable'=>TRUE,
                    'filterable'=>TRUE,
                ]);
                break;
            case 'last_modified':
                $ctrl_builder->SetColumn('last_modified',[
                    'width'=>'120',
                    'db_field'=>'last_modified',
                    'data_type'=>'datetime',
                    'type'=>'value',
                    'halign'=>'center',
                    'default_value'=>'-',
                    'label'=>Translate::GetLabel('last_modified'),
                    'sortable'=>TRUE,
                    'filterable'=>FALSE,
                ]);
                break;
            case 'last_user_full_name':
                $ctrl_builder->SetColumn('last_user_full_name',[
                    'db_field'=>'last_user_full_name',
                    'data_type'=>'string',
                    'type'=>'value',
                    'halign'=>'center',
                    'default_value'=>'-',
                    'label'=>Translate::GetLabel('modified_by'),
                    'sortable'=>TRUE,
                    'filterable'=>TRUE,
                ]);
                break;
        }//END switch
    }//END foreach
}//if(is_array($this->showInListing))

$ctrl_builder->AddAction('end_actions',[
    'dright'=>Module::DRIGHT_DELETE,
    'type'=>'DivButton',
    'ajax_command'=>"{ 'module': '{$this->class}', 'method': 'DeleteRecord', 'params': { 'id': {!id!}, 'id_template': '{!id_template!}', 'c_module': '{$cModule}', 'c_method': '{$cMethod}', 'c_target': '{$cTarget}' } }",
    'ajax_target_id'=>'errors',
    'params'=>['tooltip'=>Translate::GetButton('delete'),'class'=>NApp::$theme->GetBtnDangerClass('btn-xxs'),'icon'=>'fa fa-times','confirm_text'=>Translate::GetMessage('confirm_delete'),'conditions'=>[['field'=>'ftype','type'=>'!=','value'=>2]]],
]);
// NApp::Dlog($ctrl_builder->GetConfig(),'$ctrl_builder');