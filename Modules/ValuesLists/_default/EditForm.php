<?php
$ctrl_params = array(
    'tagid'=>'df_list_edit_tabs',
    'tabs'=>array(
        array(
            'type'=>'fixed',
            'uid'=>'def',
            'name'=>Translate::GetLabel('general'),
            'content_type'=>'control',
            'content'=>array(
                'control_type'=>'BasicForm',
                'control_params'=>array(
                    'tagid'=>'df_list_edit_form',
                    'response_target'=>'df_list_edit_errors',
                    'colsno'=>1,
                    'content'=>array(
                        array(
                            array(
                                'control_type'=>'TextBox',
                                'control_params'=>array('tagid'=>'df_list_edit_ltype','tagname'=>'ltype','value'=>$item->getProperty('ltype','','is_string'),'label'=>Translate::GetLabel('code'),'required'=>TRUE,'readonly'=>TRUE),
                            ),
                        ),
                        array(
                            array(
                                'control_type'=>'TextBox',
                                'control_params'=>array('tagid'=>'df_list_edit_name','tagname'=>'name','value'=>$item->getProperty('name','','is_string'),'label'=>Translate::GetLabel('name'),'onenterbutton'=>'df_list_edit_save'),
                            ),
                        ),
                        array(
                            array(
                                'control_type'=>'CheckBox',
                                'control_params'=>array('tagid'=>'df_list_edit_state','tagname'=>'state','value'=>$item->getProperty('state',1,'is_numeric'),'label'=>Translate::GetLabel('active'),'class'=>'pull-left'),
                            ),
                        ),
                    ),
                    'actions'=>array(
                        array(
                            'params'=>array('tagid'=>'df_list_edit_save','value'=>Translate::GetButton('save_and_close'),'icon'=>'fa fa-save','onclick'=>NApp::arequest()->Prepare("AjaxRequest('{$this->class}','AddEditRecord','id'|'{$id}'~'close'|0~df_list_edit_form:form,'df_list_edit_form')->df_list_edit_errors")),
                        ),
                        array(
                            'params'=>array('tagid'=>'df_list_edit_save','value'=>Translate::GetButton('save'),'icon'=>'fa fa-save','onclick'=>NApp::arequest()->Prepare("AjaxRequest('{$this->class}','AddEditRecord',,'df_list_edit_form')->df_list_edit_errors")),
                        ),
                    ),
                ),
            ),
        ),
        array(
            'type'=>'fixed',
            'uid'=>'values',
            'name'=>Translate::GetLabel('values'),
            'content_type'=>'ajax',
            'content'=>"AjaxRequest('{$this->class}','ValuesListing','id_list'|{$id}~'edit'|1,'{{t_target}}')->{{t_target}}",
            'reload_onchange'=>TRUE,
            'autoload'=>FALSE,
        ),
    ),
);