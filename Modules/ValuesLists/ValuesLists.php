<?php
/**
 * ValuesLists class file
 * @package    NETopes\Plugins\Modules\DForms
 * @author     George Benjamin-Schonberger
 * @copyright  Copyright (c) 2013 - 2019 AdeoTEK Software SRL
 * @license    LICENSE.md
 * @version    1.0.1.0
 * @filesource
 */
namespace NETopes\Plugins\Modules\DForms\ValuesLists;
use NETopes\Core\App\AppView;
use NETopes\Core\App\Module;
use NETopes\Core\Controls\Button;
use NETopes\Core\Data\DataProvider;
use NETopes\Core\Data\VirtualEntity;
use NETopes\Core\AppException;
use NApp;
use Translate;
/**
 * Class ValuesLists
 * @package  NETopes\Plugins\Modules\DForms
 */
class ValuesLists extends Module {
	/**
	 * description
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return void
	 * @access public
	 * @throws \NETopes\Core\AppException
	 */
	public function Listing($params = NULL) {
		$view = new AppView(get_defined_vars(),$this,'main');
		$view->AddTableView($this->GetViewFile('Listing'));
		$view->SetTitle(Translate::GetLabel('values_lists'));
		$btn_add = new Button(['value'=>Translate::GetButton('add').' '.Translate::GetLabel('values_list'),'class'=>NApp::$theme->GetBtnInfoClass(),'icon'=>'fa fa-plus','onclick'=>NApp::Ajax()->Prepare("AjaxRequest('{$this->name}','ShowAddForm')->modal")]);
		$view->AddAction($btn_add->Show());
		$view->SetTargetId('listing_content');
		$view->Render();
	}//END public function Listing
	/**
	 * description
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return void
	 * @access public
	 * @throws \NETopes\Core\AppException
	 */
	public function ShowAddForm($params = NULL) {
		$view = new AppView(get_defined_vars(),$this,'modal');
		$view->SetIsModalView(TRUE);
		$view->AddBasicForm($this->GetViewFile('AddForm'));
		$view->SetTitle(Translate::GetTitle('add_values_list'));
		$view->SetModalWidth(500);
		$view->Render();
		NApp::Ajax()->ExecuteJs("$('#df_list_add_code').focus();");
	}//END public function ShowAddForm
	/**
	 * description
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return void
	 * @access public
	 * @throws \NETopes\Core\AppException
	 */
	public function ShowEditForm($params = NULL) {
		$id = $params->getOrFail('id','is_not0_integer','Invalid record identifier!');
		$item = DataProvider::Get('Components\DForms\ValuesLists','GetItem',['for_id'=>$id]);
		$title = Translate::GetLabel('values_list').' - '.Translate::GetButton('edit').' : ' . $item['name'] . ' [ '.$item['ltype']  .' ]';
		$view = new AppView(get_defined_vars(),$this,'main');
		$view->AddTabControl($this->GetViewFile('EditForm'));
		$view->SetTitle($title);
		if(!$this->AddDRights()) {
            $btnCancel = new Button(['value'=>Translate::GetButton('back'),'class'=>NApp::$theme->GetBtnDefaultClass(),'icon'=>'fa fa-chevron-left','onclick'=>NApp::Ajax()->PrepareAjaxRequest(['module'=>$this->name,'method'=>'Listing','target'=>'main-content'])]);
            $view->AddAction($btnCancel->Show());
        }//if(!$this->AddDRights())
        $view->Render();
		NApp::Ajax()->ExecuteJs("$('#df_list_edit_code').focus();");
	}//END public function ShowEditForm
	/**
	 * description
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return void
	 * @access public
	 * @throws \NETopes\Core\AppException
	 */
	public function AddEditRecord($params = NULL) {
		$id = $params->safeGet('id',NULL,'is_integer');
		$lType = $params->safeGet('ltype',NULL,'is_notempty_string');
		$name = trim($params->safeGet('name',NULL,'is_notempty_string'));
		$target = $params->safeGet('target','','is_string');
		if(!strlen($name) || (!$id && !$lType)) {
			NApp::Ajax()->ExecuteJs("AddClassOnErrorByParent('{$target}')");
			echo Translate::GetMessage('required_fields');
			return;
		}//if(!strlen($name) || (!$id && !$lType))
		if($id) {
			$result = DataProvider::Get('Components\DForms\ValuesLists','SetItem',[
				'for_id'=>$id,
				'in_name'=>$name,
				'in_state'=>$params->safeGet('state',1,'is_integer'),
			]);
			if($result===FALSE) { throw new AppException('Unknown database error!'); }
			if($params->safeGet('close',1,'is_integer')!=1) {
				echo Translate::GetMessage('save_done').' ('.date('Y-m-d H:i:s').')';
				return;
			}//if($params->safeGet('close',1,'is_integer')!=1)
			$this->Exec('Listing',[],'main-content');
		} else {
			$result = DataProvider::Get('Components\DForms\ValuesLists','SetNewItem',array(
				'in_ltype'=>$lType,
				'in_name'=>$name,
				'in_state'=>$params->safeGet('state',1,'is_integer'),
			));
			if(!is_object($result) || !count($result)) { throw new AppException('Unknown database error!'); }
			$id = $result->first()->getProperty('inserted_id',0,'is_integer');
			if($id<=0) { throw new AppException('Unknown database error!'); }
			$this->CloseForm();
			$this->Exec('ShowEditForm',['id'=>$id],'main-content');
		}//if($id)
	}//END public function AddEditRecord
	/**
	 * description
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return void
	 * @access public
	 * @throws \NETopes\Core\AppException
	 */
	public function DeleteRecord($params = NULL) {
		$id = $params->getOrFail('id','is_not0_integer','Invalid record identifier!');
		$result = DataProvider::Get('Components\DForms\ValuesLists','UnsetItem',['for_id'=>$id]);
		if($result===FALSE) { throw new AppException('Unknown database error!'); }
		$this->Exec('Listing',[],'main-content');
	}//END public function DeleteRecord
	/**
	 * description
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return void
	 * @access public
	 * @throws \NETopes\Core\AppException
	 */
	public function ValuesListing($params = NULL) {
		$idList = $params->getOrFail('id_list','is_not0_integer','Invalid list identifier!');
		$edit = $params->safeGet('edit',0,'is_integer');
		$target = $params->safeGet('target','','is_string');
		if($edit) {
			$dgtarget = 'dg-'.$target;
			$view = new AppView(get_defined_vars(),$this,'secondary');
			$view->SetTargetId($dgtarget);
			if(!$this->AddDRights()) {
				$btnAdd = new Button([
					'tag_id'=>'df_list_edit_add',
					'value'=>Translate::GetButton('add'),
					'class'=>NApp::$theme->GetBtnInfoClass('btn-xs pull-left'),
					'icon'=>'fa fa-plus-circle',
					'onclick'=>NApp::Ajax()->PrepareAjaxRequest(['module'=>$this->name,'method'=>'ShowValueAddEditForm','target'=>'modal','params'=>['id_list'=>$idList,'target'=>$target]])]);
				$view->AddAction($btnAdd->Show());
        	}//if(!$this->AddDRights())
		} else {
			$dgtarget = $target;
			$view = new AppView(get_defined_vars(),$this,'modal');
			$view->SetIsModalView(true);
			$view->SetTitle(Translate::GetLabel('values_list').' - '.Translate::GetLabel('values'));
			$view->SetModalWidth('80%');
		}//if($edit)
		$view->AddTableView($this->GetViewFile('ValuesListing'));
		$view->Render();
	}//END public function ValuesListing
	/**
	 * description
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return void
	 * @access public
	 * @throws \NETopes\Core\AppException
	 */
	public function ShowValueAddEditForm($params = NULL) {
		$idList = $params->getOrFail('id_list','is_not0_integer','Invalid list identifier!');
		$id = $params->safeGet('id',NULL,'is_integer');
		if($id) {
			$item = DataProvider::Get('Components\DForms\ValuesLists','GetValue',['for_id'=>$id]);
		} else {
			$item = new VirtualEntity();
		}//if($id)
		$target = $params->safeGet('target','','is_string');
		$view = new AppView(get_defined_vars(),$this,'modal');
		$view->SetIsModalView(TRUE);
		$view->AddBasicForm($this->GetViewFile('ValueAddEditForm'));
		$view->SetTitle(Translate::GetTitle('add_values_list').' - '.Translate::GetLabel('value').' - '.Translate::Get($id ? 'button_edit' : 'button_add'));
		$view->SetModalWidth(500);
		$view->Render();
		NApp::Ajax()->ExecuteJs("$('#df_lv_ae_value').focus();");
	}//END public function ShowValueAddEditForm
	/**
	 * description
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return void
	 * @access public
	 * @throws \NETopes\Core\AppException
	 */
	public function AddEditValueRecord($params = NULL){
		$idList = $params->getOrFail('id_list','is_not0_integer','Invalid list identifier!');
		$id = $params->safeGet('id',NULL,'is_integer');
		$value = trim($params->safeGet('value','','is_string'));
		$name = $params->safeGet('name',NULL,'is_notempty_string');
		$target = $params->safeGet('target','','is_string');
		if(!strlen($value)) {
			NApp::Ajax()->ExecuteJs("AddClassOnErrorByParent('{$target}')");
			echo Translate::GetMessage('required_fields');
			return;
		}//if(!strlen($value))
		if($id) {
			$result = DataProvider::Get('Components\DForms\ValuesLists','SetValue',[
				'for_id'=>$id,
				'in_value'=>$value,
				'in_name'=>$name,
				'in_state'=>$params->safeGet('state',1,'is_integer'),
				'in_implicit'=>$params->safeGet('implicit',0,'is_integer'),
			]);
			if($result===FALSE) { throw new AppException('Unknown database error!'); }
		} else {
			$result = DataProvider::GetArray('Components\DForms\ValuesLists','SetNewValue',[
				'list_id'=>$idList,
				'in_value'=>$value,
				'in_name'=>$name,
				'in_state'=>$params->safeGet('state',1,'is_integer'),
				'in_implicit'=>$params->safeGet('implicit',0,'is_integer'),
			]);
			if(!is_object($result) || !count($result) || $result->first()->getProperty('inserted_id',0,'is_integer')<=0) { throw new AppException('Unknown database error!'); }
		}//if($id)
		$this->CloseForm();
		$ctarget = $params->safeGet('ctarget','','is_string');
		$this->Exec('ValuesListing',['id_list'=>$idList,'edit'=>1,'target'=>$ctarget],$ctarget);
	}//END public function AddEditValueRecord
	/**
	 * description
	 * @param \NETopes\Core\App\Params|array|null $params Parameters
	 * @return void
	 * @access public
	 * @throws \NETopes\Core\AppException
	 */
	public function DeleteValueRecord($params = NULL) {
		$id = $params->getOrFail('id','is_not0_integer','Invalid record identifier!');
		$idList = $params->safeGet('id_list','is_not0_integer','Invalid list identifier!');
		$result = DataProvider::Get('Components\DForms\ValuesLists','UnsetValue',['for_id'=>$id]);
		if($result===FALSE) { throw new AppException('Unknown database error!'); }
		$target = $params->safeGet('target','','is_string');
		$this->Exec('ValuesListing',['id_list'=>$idList,'edit'=>1,'target'=>$target],$target);
	}//END public function DeleteValueRecord
}//END class ValuesLists extends Module