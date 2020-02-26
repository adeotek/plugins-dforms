<?php
/** @var string $listingTarget */
/** @var int|null $templateId */
/** @var string|null $templateCode */
/** @var \NETopes\Core\Data\DataSet|array $fTypes */
$ctrl_params=[
    'drights_uid'=>$this->module->dRightsUid,
    'persistent_state'=>TRUE,
    'target'=>$listingTarget,
    'alternate_row_color'=>TRUE,
    'scrollable'=>FALSE,
    'with_filter'=>TRUE,
    'with_pagination'=>TRUE,
    'sortby'=>['column'=>'CREATE_DATE','direction'=>'DESC'],
    'qsearch'=>'for_text',
    'ds_class'=>'Plugins\DForms\Instances',
    'ds_method'=>'GetInstancesList',
    'ds_params'=>['for_id'=>NULL,'template_id'=>$templateId,'for_template_code'=>$templateCode,'for_state'=>NULL,'for_text'=>NULL],
    'auto_load_data'=>TRUE,
    'columns'=>[
        'actions'=>[
            'type'=>'actions',
            'visual_count'=>2,
            'actions'=>[
                [
                    'type'=>'DivButton',
                    'ajax_command'=>"{ 'module': '{$this->class}', 'method': 'ShowEditForm', 'params': { 'id': {!id!}, 'id_template': '{!id_template!}', 'c_module': '{$this->class}', 'c_method': 'GlobalListing' } }",
                    'ajax_target_id'=>'main-content',
                    'params'=>['tooltip'=>Translate::GetButton('edit'),'class'=>NApp::$theme->GetBtnPrimaryClass('btn-xxs'),'icon'=>'fa fa-pencil-square-o'],
                ],
                [
                    'type'=>'DivButton',
                    'ajax_command'=>"{ 'module': '{$this->class}', 'method': 'ShowViewForm', 'params': { 'id': {!id!}, 'id_template': '{!id_template!}', 'is_modal': 1 } }",
                    'ajax_target_id'=>'modal',
                    'params'=>['tooltip'=>Translate::GetButton('view'),'class'=>NApp::$theme->GetBtnInfoClass('btn-xxs pull-right'),'icon'=>'fa fa-eye'],
                ],
            ],
        ],
        'template_code'=>[
            'db_field'=>'template_code',
            'data_type'=>'numeric',
            'type'=>'value',
            'format'=>'integer',
            'halign'=>'center',
            'label'=>Translate::GetLabel('template_code'),
            'sortable'=>TRUE,
            'filterable'=>TRUE,
        ],
        'template_name'=>[
            'db_field'=>'template_name',
            'data_type'=>'string',
            'type'=>'value',
            'halign'=>'left',
            'label'=>Translate::GetLabel('template_name'),
            'sortable'=>TRUE,
            'filterable'=>TRUE,
        ],
        'version'=>[
            'db_field'=>'version',
            'data_type'=>'numeric',
            'type'=>'value',
            'format'=>'integer',
            'halign'=>'center',
            'label'=>Translate::GetLabel('version'),
            'sortable'=>TRUE,
            'filterable'=>TRUE,
        ],
        'ftype'=>[
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
        ],
        'state'=>[
            'width'=>'60',
            'db_field'=>'state',
            'data_type'=>'numeric',
            'type'=>'control',
            'control_type'=>'CheckBox',
            'control_params'=>[
                'container'=>FALSE,'no_label'=>TRUE,'tag_id'=>'df_instance_update_state','value'=>'{!state!}',
                'onchange_ajax_command'=>"{ 'module': '{$this->name}', 'method': 'EditRecordState', 'params': { 'id': '{!id!}', 'state': '{nGet|df_instance_update_state_{!id!}:value}' } }",
                'onchange_target_id'=>'errors',
            ],
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
        ],
        'create_date'=>[
            'width'=>'125',
            'db_field'=>'create_date',
            'data_type'=>'datetime',
            'type'=>'value',
            'halign'=>'center',
            'label'=>Translate::GetLabel('created_at'),
            'sortable'=>TRUE,
            'filterable'=>FALSE,
        ],
        'user_full_name'=>[
            'db_field'=>'user_full_name',
            'data_type'=>'string',
            'type'=>'value',
            'halign'=>'center',
            'label'=>Translate::GetLabel('created_by'),
            'sortable'=>TRUE,
            'filterable'=>TRUE,
        ],
        'last_modified'=>[
            'width'=>'125',
            'db_field'=>'last_modified',
            'data_type'=>'datetime',
            'type'=>'value',
            'halign'=>'center',
            'default_value'=>'-',
            'label'=>Translate::GetLabel('last_modified'),
            'sortable'=>TRUE,
            'filterable'=>FALSE,
        ],
        'last_user_full_name'=>[
            'db_field'=>'last_user_full_name',
            'data_type'=>'string',
            'type'=>'value',
            'halign'=>'center',
            'default_value'=>'-',
            'label'=>Translate::GetLabel('modified_by'),
            'sortable'=>TRUE,
            'filterable'=>TRUE,
        ],
        'end_actions'=>[
            'type'=>'actions',
            'visual_count'=>1,
            'actions'=>[
                [
                    'type'=>'DivButton',
                    'ajax_command'=>"{ 'module': '{$this->class}', 'method': 'DeleteRecord', 'params': { 'id': {!id!}, 'id_template': '{!id_template!}', 'c_module': '{$this->class}', 'c_method': 'GlobalListing' } }",
                    'ajax_target_id'=>'errors',
                    'params'=>['tooltip'=>Translate::GetButton('delete'),'class'=>NApp::$theme->GetBtnDangerClass('btn-xxs'),'icon'=>'fa fa-times','confirm_text'=>Translate::GetMessage('confirm_delete'),'conditions'=>[['field'=>'ftype','type'=>'!=','value'=>2]]],
                ],
            ],
        ],
    ],
];