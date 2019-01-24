<?php
use NETopes\Core\App\ModulesProvider;
use NETopes\Core\Controls\CheckBox;
use NETopes\Core\Controls\EditBox;
use NETopes\Core\Controls\HiddenInput;
use NETopes\Core\Controls\SmartComboBox;
use NETopes\Core\Controls\TextBox;

	$cclass = $fieldType->getProperty('class','','is_string');
	$cdatatype = $fieldType->getProperty('data_type','','is_string');
	$cparams = $item->getProperty('params','','is_string');
	$vfields = [];
	$hfields = [];
	switch($cclass) {
		case 'FormTitle':
		case 'FormSeparator':
		case 'FormSubTitle':
		case 'Message':
			$ctrl = new HiddenInput(['tagid'=>'dft_fp_itype','tagname'=>'itype','value'=>$item->getProperty('itype',1,'is_integer')]);
			$hfields[] = $ctrl->Show();
			$ctrl = new HiddenInput(['tagid'=>'dft_fp_name','tagname'=>'name','value'=>$item->getProperty('name',\NETopes\Core\AppSession::GetNewUID($idTemplate.$cclass),'is_string')]);
			$hfields[] = $ctrl->Show();
			if($cclass=='FormTitle') {
				$ctrl = new EditBox(array('label'=>Translate::GetLabel('title'),'required'=>TRUE,'tagid'=>'dft_fp_label','tagname'=>'label','value'=>$item->getProperty('label','','is_string'),'height'=>50,'placeholder'=>'Title'));
				$vfields[] = $ctrl->Show();
			} elseif($cclass=='FormSubTitle') {
				$ctrl = new EditBox(array('label'=>Translate::GetLabel('sub_title'),'required'=>TRUE,'tagid'=>'dft_fp_label','tagname'=>'label','value'=>$item->getProperty('label','','is_string'),'height'=>50,'placeholder'=>'Sub-title'));
				$vfields[] = $ctrl->Show();
			} elseif($cclass=='Message') {
				$ctrl = new TextBox(array('label'=>Translate::GetLabel('short_text').' (max: 255)','tagid'=>'dft_fp_label','tagname'=>'label','value'=>$item->getProperty('label','','is_string'),'placeholder'=>'Field displayed label'));
				$vfields[] = $ctrl->Show();
				$ctrl = new EditBox(array('label'=>Translate::GetLabel('long_text'),'tagid'=>'dft_fp_description','tagname'=>'description','value'=>$item->getProperty('description','','is_string'),'height'=>50));
				$vfields[] = $ctrl->Show();
			} else {
				$ctrl = new HiddenInput(['tagid'=>'dft_fp_label','tagname'=>'label','value'=>$item->getProperty('label',$cclass,'is_string')]);
				$hfields[] = $ctrl->Show();
			}//if($cclass=='FormTitle')
			$ctrl = new HiddenInput(['tagid'=>'dft_fp_required','tagname'=>'required','value'=>$item->getProperty('required',0,'is_integer')]);
			$hfields[] = $ctrl->Show();
			$ctrl = new HiddenInput(['tagid'=>'dft_fp_listing','tagname'=>'listing','value'=>$item->getProperty('listing',0,'is_integer')]);
			$hfields[] = $ctrl->Show();
			break;
		case 'BasicForm':
			$ctrl = new SmartComboBox(array('label'=>Translate::GetLabel('field_type'),'tagid'=>'dft_fp_itype','tagname'=>'itype',
				'valfield'=>'id',
				'displayfield'=>'name',
				'selectedvalue'=>$item->getProperty('itype',1,'is_integer'),
				'allow_clear'=>FALSE,
				'load_type'=>'database',
				'data_source'=>array(
					'ds_class'=>'_Custom\Offline',
					'ds_method'=>'GetDynamicFormsFieldsITypes',
				),
			));
			$vfields[] = $ctrl->Show();
			$ctrl = new HiddenInput(['tagid'=>'dft_fp_name','tagname'=>'name','value'=>$item->getProperty('name',NULL,'is_string')]);
			$hfields[] = $ctrl->Show();
			$ctrl = new HiddenInput(['tagid'=>'dft_fp_label','tagname'=>'label','value'=>$item->getProperty('label',$cclass,'is_string')]);
			$hfields[] = $ctrl->Show();
			$ctrl = new HiddenInput(['tagid'=>'dft_fp_required','tagname'=>'required','value'=>$item->getProperty('required',0,'is_integer')]);
			$hfields[] = $ctrl->Show();
			$ctrl = new HiddenInput(['tagid'=>'dft_fp_listing','tagname'=>'listing','value'=>$item->getProperty('listing',0,'is_integer')]);
			$hfields[] = $ctrl->Show();
			$ctrl = new SmartComboBox(array('label'=>Translate::GetLabel('form'),'tagid'=>'dft_fp_id_sub_form','tagname'=>'id_sub_form','required'=>TRUE,'disabled'=>(is_numeric($id) && $id>0),
				'valfield'=>'id',
				'displayfield'=>'full_name',
				'selectedvalue'=>$item->getProperty('id_sub_form',1,'is_integer'),
				'allow_clear'=>TRUE,
				'placeholder'=>Translate::GetLabel('please_select_template'),
				'load_type'=>'database',
				'data_source'=>array(
					'ds_class'=>'Plugins\DForms\Templates',
					'ds_method'=>'GetItems',
					'ds_params'=>array('for_id'=>NULL,'for_validated'=>1,'for_state'=>1,'for_text'=>NULL,'for_ftype'=>1,'exclude_id'=>$idTemplate),
				),
			));
			$vfields[] = $ctrl->Show();
			break;
		default:
			$ctrl = new SmartComboBox(array('label'=>Translate::GetLabel('field_type'),'tagid'=>'dft_fp_itype','tagname'=>'itype',
				'valfield'=>'id',
				'displayfield'=>'name',
				'selectedvalue'=>$item->getProperty('itype',1,'is_numeric'),
				'allow_clear'=>FALSE,
				'load_type'=>'database',
				'data_source'=>array(
					'ds_class'=>'_Custom\Offline',
					'ds_method'=>'GetDynamicFormsFieldsITypes',
				),
			));
			$vfields[] = $ctrl->Show();
			$ctrl = new TextBox(array('label'=>Translate::GetLabel('field_name'),'required'=>TRUE,'tagid'=>'dft_fp_name','tagname'=>'name','value'=>$item->getProperty('name','','is_string'),'placeholder'=>'Field name (no spaces)'));
			$vfields[] = $ctrl->Show();
			$ctrl = new TextBox(array('label'=>Translate::GetLabel('field_label'),'required'=>TRUE,'tagid'=>'dft_fp_label','tagname'=>'label','value'=>$item->getProperty('label','','is_string'),'placeholder'=>'Field displayed label'));
			$vfields[] = $ctrl->Show();
			$ctrl = new CheckBox(array('label'=>Translate::GetLabel('required_field'),'tagid'=>'dft_fp_required','tagname'=>'required','value'=>$item->getProperty('required',0,'is_integer'),'class'=>'pull-left'));
			$vfields[] = $ctrl->Show();
			$ctrl = new CheckBox(array('label'=>Translate::GetLabel('show_in_listing'),'tagid'=>'dft_fp_listing','tagname'=>'listing','value'=>$item->getProperty('listing',0,'is_integer'),'class'=>'pull-left'));
			$vfields[] = $ctrl->Show();
			if(in_array($cclass,['SmartComboBox','GroupCheckBox'])) {
				$ctrl = new SmartComboBox(array('label'=>Translate::GetLabel('values_list'),'tagid'=>'dft_fp_values_list','tagname'=>'id_values_list',
				'placeholder'=>Translate::GetLabel('none'),
				'valfield'=>'id',
				'displayfield'=>'name',
				'selectedvalue'=>$item->getProperty('id_values_list',NULL,'is_string'),
				'selectedtext'=>$item->getProperty('values_list','','is_string'),
				'allow_clear'=>TRUE,
				'load_type'=>'database',
				'data_source'=>array(
					'ds_class'=>'Plugins\DForms\ValuesLists',
					'ds_method'=>'GetItems',
					'ds_params'=>array('for_id'=>NULL,'for_state'=>1,'for_text'=>NULL),
				)));
				$vfields[] = $ctrl->Show();
			}//if(in_array($cclass,['SmartComboBox']))
			break;
	}//END switch

	$ctrl_params = [
		'tagid'=>'dft_fp_form',
		'response_target'=>'dft_fp_form_errors',
		'colsno'=>1,
		'content'=>[],
	];
	foreach($vfields as $f) {
		$ctrl_params['content'][] = [
			[
				'control_type'=>'CustomControl',
				'control_params'=>['value'=>$f],
			],
		];
	}//END foreach
	if(count($hfields)) {
		$ctrl_params['content'][] = [
			[
				'control_type'=>'CustomControl',
				'control_params'=>['value'=>implode("\n",$hfields)],
			],
		];
	}//if(count($hfields))

    $tab_ctrl = ModulesProvider::Exec('Plugins\DForms\Controls\Controls','GetControlPropertiesTab',array('id_control'=>$idControl,'data'=>$cparams,'target'=>'dft_fp_properties_tab'));
    if(is_object($tab_ctrl)) {
        $ctrl_params['content'][] = ['separator'=>'subtitle','value'=>Translate::GetLabel('control_properties')];
        $ctrl_params['content'][] = [
            [
                'control_type'=>'CustomControl',
                'control_params'=>['value'=>$tab_ctrl->Show()],
            ],
        ];
    }//if(is_object($tab_ctrl))

	$ctrl_params['actions'] = [
	    [
	        'params'=>['value'=>Translate::GetButton('save'),'icon'=>'fa fa-save','onclick'=>NApp::arequest()->Prepare("AjaxRequest('{$this->class}','AddEditContentElementRecord',
                    'id_template'|{$idTemplate}
                    ~'pindex'|'{$pindex}'
                    ~'id_item'|'{$id}'
                    ~'class'|'{$cclass}'
                    ~'data_type'|'{$cdatatype}'
                    ~'frow'|'{$frow}'
                    ~'fcol'|'{$fcol}'
                    ~'id_control'|'{$idControl}'
                    ~dft_fp_form:form
                    ~'properties'|dft_fp_properties_tab:form
                ,'dft_fp_form')->dft_fp_form_errors")],
        ],
        [
            'type'=>'CloseModal',
            'params'=>['value'=>Translate::GetButton('cancel'),'icon'=>'fa fa-ban'],
        ],
    ];




