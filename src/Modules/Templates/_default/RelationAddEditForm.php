<?php
$ctrl_params=[
    'tag_id'=>'df_template_rel_ae_form',
    'response_target'=>'df_template_rel_ae_errors',
    'cols_no'=>1,
    'content'=>[
        [
            [
                'control_type'=>'SmartComboBox',
                'control_params'=>['tag_id'=>'df_template_rel_ae_type','label'=>Translate::GetLabel('type'),'required'=>TRUE,'disabled'=>(is_numeric($id) && $id>0),
                    'value_field'=>'id',
                    'display_field'=>'name',
                    'selected_value'=>$item->getProperty('id_relation_type',NULL,'is_numeric'),
                    'allow_clear'=>TRUE,
                    'placeholder'=>Translate::GetLabel('please_select'),
                    'load_type'=>'database',
                    'data_source'=>[
                        'ds_class'=>'Plugins\DForms\Relations',
                        'ds_method'=>'GetTypeItems',
                        'ds_params'=>['for_state'=>1],
                        'ds_extra_params'=>['sort'=>['NAME'=>'ASC']],
                    ],
                ],
            ],
        ],
        [
            [
                'control_type'=>'TextBox',
                'control_params'=>['tag_id'=>'df_template_rel_ae_name','value'=>$item->getProperty('name','','is_string'),'label'=>Translate::GetLabel('name'),'required'=>TRUE,'onenter_button'=>'df_template_rel_ae_save'],
            ],
        ],
        [
            [
                'control_type'=>'TextBox',
                'control_params'=>['tag_id'=>'df_template_rel_ae_key','value'=>$item->getProperty('key','','is_string'),'label'=>Translate::GetLabel('key'),'required'=>TRUE,'onenter_button'=>'df_template_rel_ae_save'],
            ],
        ],
        [
            [
                'control_type'=>'SmartComboBox',
                'control_params'=>['tag_id'=>'df_template_rel_ae_rtype','label'=>Translate::GetLabel('value_type'),'required'=>TRUE,
                    'value_field'=>'id',
                    'display_field'=>'name',
                    'selected_value'=>$item->getProperty('rtype',NULL,'is_numeric'),
                    'load_type'=>'database',
                    'data_source'=>[
                        'ds_class'=>'_Custom\DFormsOffline',
                        'ds_method'=>'GetDynamicFormsRelationsRTypes',
                    ],
                ],
            ],
        ],
        [
            [
                'control_type'=>'SmartComboBox',
                'control_params'=>['tag_id'=>'df_template_rel_ae_utype','label'=>Translate::GetLabel('usage_type'),'required'=>TRUE,
                    'value_field'=>'id',
                    'display_field'=>'name',
                    'selected_value'=>$item->getProperty('utype',NULL,'is_numeric'),
                    'load_type'=>'database',
                    'data_source'=>[
                        'ds_class'=>'_Custom\DFormsOffline',
                        'ds_method'=>'GetDynamicFormsRelationsUTypes',
                    ],
                ],
            ],
        ],
        [
            [
                'control_type'=>'CheckBox',
                'control_params'=>['tag_id'=>'df_template_rel_ae_required','value'=>$item->getProperty('required',0,'is_numeric'),'label'=>Translate::GetLabel('required'),'class'=>'pull-left'],
            ],
        ],
    ],
    'actions'=>[
        [
            'params'=>['value'=>Translate::GetButton('save'),'icon'=>'fa fa-save','onclick'=>NApp::Ajax()->LegacyPrepare("AjaxRequest('{$this->class}','AddEditRelationRecord',
                'id_template'|{$idTemplate}
                ~'id'|'{$id}'
                ~'type'|df_template_rel_ae_type:value
                ~'rtype'|df_template_rel_ae_rtype:value
                ~'utype'|df_template_rel_ae_utype:value
                ~'name'|df_template_rel_ae_name:value
                ~'key'|df_template_rel_ae_key:value
                ~'required'|df_template_rel_ae_required:value
                ~'ctarget'|'{$target}'
            ,'df_template_rel_ae_form')->df_template_rel_ae_errors")],
        ],
        [
            'type'=>'CloseModal',
            'params'=>['value'=>Translate::GetButton('cancel'),'icon'=>'fa fa-ban'],
        ],
    ],
];