<?php
/**
 * Dynamic forms Instances class file
 *
 * @package    NETopes\Plugins\Modules\DForms
 * @author     George Benjamin-Schonberger
 * @copyright  Copyright (c) 2013 - 2019 AdeoTEK Software SRL
 * @license    LICENSE.md
 * @version    1.2.1.0
 * @filesource
 */
namespace NETopes\Plugins\DForms\Modules\Instances;
use NApp;
use NETopes\Core\App\AppView;
use NETopes\Core\App\Module;
use NETopes\Core\App\ModulesProvider;
use NETopes\Core\App\Params;
use NETopes\Core\AppException;
use NETopes\Core\AppSession;
use NETopes\Core\Controls\Button;
use NETopes\Core\Controls\IControlBuilder;
use NETopes\Core\Controls\TableView;
use NETopes\Core\Data\DataProvider;
use NETopes\Core\Data\IEntity;
use NETopes\Core\Reporting\PdfBuilder;
use Translate;

/**
 * Class Instances
 *
 * @package NETopes\Plugins\DForms\Modules\Instances
 */
class Instances extends Module {
    /**
     * string Denied rights GUID
     */
    const DRIGHTS_UID='';
    /**
     * string Denied rights global GUID
     */
    const DRIGHTS_UID_GLOBAL='';
    /**
     * @var integer Dynamic form template ID
     */
    public $templateId=NULL;
    /**
     * @var integer Dynamic form template code (numeric)
     */
    public $templateCode=NULL;
    /**
     * @var bool Flag for modal add/edit forms
     */
    public $formAsModal=FALSE;
    /**
     * @var bool Flag for modal add/edit forms
     */
    public $viewAsModal=FALSE;
    /**
     * @var bool Flag for throwing exceptions on errors
     */
    public $errorsAsException=FALSE;
    /**
     * @var string AppView container type
     */
    public $containerType='main';
    /**
     * @var bool Render actions in TableView control
     */
    public $inListingActions=TRUE;
    /**
     * @var string Forms actions location (form/container/after)
     */
    public $actionsLocation='form';
    /**
     * @var string|null Forms back action location (form/container/after)
     */
    public $backActionLocation='container';
    /**
     * @var array List of header fields to be displayed in Listing
     */
    public $showInListing=['template_code','template_name','create_date','user_full_name','last_modified','last_user_full_name'];
    /**
     * @var bool Flag for showing print action in listing
     */
    public $listingPrintAction=TRUE;
    /**
     * @var bool Flag for showing print action in edit/view forms
     */
    public $formPrintAction=TRUE;
    /**
     * @var string|null Print action location (form/container/after)
     */
    public $printActionLocation='container';
    /**
     * @var string Print URL virtual path
     */
    public $printUrlVirtualPath='cdn';
    /**
     * @var string|null Print relative URL
     */
    public $printUrl=NULL;
    /**
     * @var array List CSS styles to be used for generating view HTML
     */
    protected $htmlStyles=[
        'table_attr'=>'border="0" ',
        'table_style'=>'width: 100%;',
        'title_style'=>'font-size: 16px; font-weight: bold; margin: 0;',//margin-bottom: 20px;
        'subtitle_style'=>'font-size: 14px; font-weight: bold; margin: 0;',//margin-bottom: 10px;
        'label_style'=>'font-size: 14px; font-style: italic;',
        'label_value_sep'=>':&nbsp;&nbsp;&nbsp;&nbsp;',
        'relation_style'=>'font-weight: bold;',
        'value_style'=>'font-size: 14px;',
        'msg_style'=>'font-style: italic;',
        'empty_value'=>'&nbsp;-&nbsp;',
    ];

    /**
     * Module class initializer
     *
     * @return void
     */
    protected function _Init() {
        $this->viewsExtension='.php';
    }//END protected function _Init

    /**
     * @param \NETopes\Core\App\Params $params
     * @throws \NETopes\Core\AppException
     */
    protected function PrepareConfigParams(Params $params): void {
        $this->templateId=$params->safeGet('id_template',$this->templateId,'is_not0_integer');
        $this->templateCode=$params->safeGet('template_code',$this->templateCode,'is_not0_integer');
        if(!$this->templateId && !$this->templateCode) {
            throw new AppException('Invalid DynamicForm template identifier!');
        }
        $this->formAsModal=$params->safeGet('forms_as_modal',$this->formAsModal,'is_integer');
        $this->viewAsModal=$params->safeGet('view_as_modal',$this->viewAsModal,'is_integer');
        $this->containerType=$params->safeGet('container_type',$this->containerType,'is_string');
        $this->inListingActions=$params->safeGet('in_listing_actions',$this->inListingActions,'bool');
        $this->actionsLocation=$params->safeGet('actions_location',$this->actionsLocation,'is_notempty_string');
        $this->backActionLocation=$params->safeGet('back_action_location',$this->backActionLocation,'is_notempty_string');
        $this->showInListing=$params->safeGet('show_in_listing',$this->showInListing,'is_array');
    }//END protected function PrepareConfigParams

    /**
     * @param \NETopes\Core\App\Params $params
     * @return int|null
     * @throws \NETopes\Core\AppException
     */
    protected function GetInstanceId(Params $params): ?int {
        // NApp::Dlog($params->toArray(),'GetInstanceId');
        $this->PrepareConfigParams($params);
        $instanceId=$params->safeGet('id',NULL,'is_not0_integer');
        if(!$instanceId) {
            /** @var \NETopes\Core\Data\VirtualEntity $template */
            $template=DataProvider::Get('Plugins\DForms\Instances','GetTemplate',[
                'for_id'=>$this->templateId,
                'for_code'=>$this->templateCode,
                'instance_id'=>NULL,
                'for_state'=>1,
            ]);
            if(!$template instanceof IEntity) {
                throw new AppException('Invalid DynamicForm template!');
            }
            $this->templateId=$template->getProperty('id',NULL,'is_integer');
            $this->templateCode=$template->getProperty('code',NULL,'is_integer');
            if(!$this->templateId) {
                throw new AppException('Invalid DynamicForm template!');
            }

            if(!$instanceId && $template->getProperty('ftype',0,'is_integer')==2 && $params instanceof Params) {
                $instance=InstancesHelpers::GetSingletonInstance($this->templateId,$params);
                $instanceId=$instance->getProperty('id',NULL,'is_integer');
            }//if(!$instanceId && $template->getProperty('ftype',0,'is_integer')==2 && $params instanceof Params)
        }
        return $instanceId;
    }//END protected function GetInstanceId

    /**
     * @param \NETopes\Core\App\Params $params Parameters object
     * @return void
     * @throws \NETopes\Core\AppException
     */
    public function Listing(Params $params) {
        // NApp::Dlog($params->toArray(),'Listing');
        $this->PrepareConfigParams($params);
        $template=DataProvider::Get('Plugins\DForms\Instances','GetTemplate',[
            'for_id'=>$this->templateId,
            'for_code'=>$this->templateCode,
            'instance_id'=>NULL,
            'for_state'=>1,
        ]);
        $this->templateId=$template->getProperty('id',NULL,'is_integer');
        if(!$this->templateId) {
            throw new AppException('Invalid DynamicForm template!');
        }
        $relationsData=InstancesHelpers::GetRelationsData($this->templateId,NULL,$params);
        $relationsValues=InstancesHelpers::GetRelationsFilterParam($relationsData);
        $fields=DataProvider::Get('Plugins\DForms\Instances','GetFields',[
            'template_id'=>$this->templateId,
            'for_template_code'=>NULL,
            'for_listing'=>1,
        ]);
        $fTypes=DataProvider::GetKeyValue('_Custom\Offline','GetDynamicFormsTemplatesFTypes');
        $target=$params->safeGet('target',$params->safeGet('_target_id','main-content','is_notempty_string'),'is_notempty_string');
        $cModule=$params->safeGet('c_module',$this->class,'is_notempty_string');
        $cMethod=$params->safeGet('c_method',call_back_trace(0),'is_notempty_string');
        $cTarget=$params->safeGet('c_target',$target,'is_notempty_string');

        $listingTarget=$target.'_listing';
        $listingAddActionRelations=InstancesHelpers::GetAddActionRelationsParams($relationsData);
        $listingAddAction=[
            'value'=>Translate::GetButton('add'),
            'class'=>NApp::$theme->GetBtnPrimaryClass(),
            'icon'=>'fa fa-plus',
            'onclick'=>NApp::Ajax()->Prepare("{ 'module': '{$this->name}', 'method': 'ShowAddForm', 'params': { 'id_template': '{$this->templateId}',{$listingAddActionRelations} 'c_module': '{$cModule}', 'c_method': '{$cMethod}', 'c_target': '{$cTarget}', 'target': '{$target}' } }",$target),
        ];

        $view=new AppView(get_defined_vars(),$this,$this->containerType);
        $view->SetTitle($params->safeGet('title',$template->getProperty('name','','is_string'),'is_string'));
        $view->SetTargetId($listingTarget);
        $view->AddControlBuilderContent($this->GetViewFile('Listing'),TableView::class);
        if(!$this->inListingActions) {
            if(!$this->AddDRights()) {
                $btnAdd=new Button($listingAddAction);
                $view->AddAction($btnAdd->Show());
            }//if(!$this->AddDRights())
        }
        $view->Render();
    }//END public function Listing

    /**
     * @param \NETopes\Core\App\Params $params Parameters object
     * @return void
     * @throws \NETopes\Core\AppException
     */
    public function GlobalListing(Params $params) {
        $fTypes=DataProvider::GetKeyValue('_Custom\Offline','GetDynamicFormsTemplatesFTypes');
        $templateId=$templateCode=NULL;
        $listingTarget='listing-content';
        $view=new AppView(get_defined_vars(),$this,'main');
        $view->SetTitle('dynamic_forms_instances');
        $view->SetTargetId($listingTarget);
        $view->AddTableView($this->GetViewFile('GlobalListing'));
        $view->Render();
    }//END public function GlobalListing

    /**
     * @param \NETopes\Core\App\Params $params
     * @param string                   $formId
     * @throws \NETopes\Core\AppException
     */
    protected function ShowAddEditErrors(Params $params,string $formId): void {
        $message=NULL;
        $relationsErrors=$params->safeGet('df_relations_errors',[],'is_array');
        foreach($relationsErrors as $error) {
            $type=get_array_value($error,'type','','is_string');
            $name=get_array_value($error,'name','','is_string');
            $message.='<li>'.$name.': '.Translate::GetError('required_field').'</li>';
            if(strlen($type)=='required_field') {
                NApp::Ajax()->ExecuteJs("AddClassOnErrorByName('{$formId}','".get_array_value($error,'key','','is_string')."')");
            }//if(strlen($type)=='required_field')
        }//END foreach
        $fieldsErrors=$params->safeGet('df_fields_errors',[],'is_array');
        foreach($fieldsErrors as $error) {
            // $type=get_array_value($error,'type','','is_string');
            $name=get_array_value($error,'label','','is_string');
            $fieldUid=get_array_value($error,'uid',NULL,'is_string');
            $message.='<li>'.$name.': '.Translate::GetError('required_field').'</li>';
            if(strlen($fieldUid)) {
                NApp::Ajax()->ExecuteJs("AddClassOnErrorByName('{$formId}','{$fieldUid}')");
            }//if(strlen($fieldUid))
        }//END foreach
        if(strlen($message)) {
            if($this->errorsAsException) {
                throw new AppException('<ul class="errors-list">'.$message.'</ul>');
            } else {
                echo '<ul class="errors-list">'.$message.'</ul>';
            }
        } else {
            NApp::Ajax()->ExecuteJs("AddClassOnErrorByParent('{$formId}')");
            if($this->errorsAsException) {
                throw new AppException(Translate::GetMessage('required_fields'));
            } else {
                echo Translate::GetMessage('required_fields');
            }
        }//if(strlen($message))
    }//END protected function ShowAddEditErrors

    /**
     * @param \NETopes\Core\App\Params $params Parameters object
     * @return void
     * @throws \NETopes\Core\AppException
     */
    public function ShowAddEditForm(Params $params) {
        // NApp::Dlog($params->toArray(),'ShowAddEditForm');
        $this->PrepareConfigParams($params);
        $instanceId=$params->safeGet('id',NULL,'is_not0_integer');
        /** @var \NETopes\Core\Data\VirtualEntity $template */
        $template=DataProvider::Get('Plugins\DForms\Instances','GetTemplate',[
            'for_id'=>$this->templateId,
            'for_code'=>$this->templateCode,
            'instance_id'=>$instanceId,
            'for_state'=>1,
        ]);
        if(!$template instanceof IEntity) {
            throw new AppException('Invalid DynamicForm template!');
        }
        $this->templateId=$template->getProperty('id',NULL,'is_integer');
        $this->templateCode=$template->getProperty('code',NULL,'is_integer');
        if(!$this->templateId) {
            throw new AppException('Invalid DynamicForm template!');
        }
        if(!$instanceId && $template->getProperty('ftype',0,'is_integer')==2 && $params instanceof Params) {
            $instance=InstancesHelpers::GetSingletonInstance($this->templateId,$params);
            $instanceId=$instance->getProperty('id',NULL,'is_integer');
        }//if(!$instanceId && $template->getProperty('ftype',0,'is_integer')==2 && $params instanceof Params)
        $viewOnly=$params->safeGet('view_only',FALSE,'bool');
        $noRedirect=$params->safeGet('no_redirect',FALSE,'bool');
        $target=$params->safeGet('target',$params->safeGet('_target_id','main-content','is_notempty_string'),'is_notempty_string');
        $cModule=$params->safeGet('c_module',$this->name,'is_notempty_string');
        $cMethod=$params->safeGet('c_method','ShowAddEditForm','is_notempty_string');
        $cTarget=$params->safeGet('c_target',$target,'is_notempty_string');
        $customActions=$params->safeGet('custom_actions',[],'is_array');

        $builder=InstancesHelpers::PrepareForm($params,$template,$instanceId);
        if(!$builder instanceof IControlBuilder) {
            throw new AppException('Invalid DynamicForm configuration!');
        }
        $ctrl_params=$builder->GetConfig();
        $controlClass=get_array_value($ctrl_params,'control_class','','is_string');
        $aeSaveInstanceMethod='SaveInstance';
        $tName=get_array_value($ctrl_params,'tname',microtime(),'is_string');
        $fTagId=get_array_value($ctrl_params,'tag_id','','is_string');

        if($controlClass!='BasicForm' && $this->actionsLocation=='form') {
            $this->actionsLocation='container';
        }
        if($this->formAsModal) {
            $view=new AppView(get_defined_vars(),$this,'modal');
            $view->SetIsModalView(TRUE);
            $view->SetModalWidth('80%');
            $view->SetTitle($template->getProperty('name','','is_string'));
        } else {
            $view=new AppView(get_defined_vars(),$this,$this->containerType);
            $view->SetTitle($template->getProperty('name','','is_string'));
        }//if($this->formAsModal)
        $fResponseTarget=get_array_value($ctrlParams,'response_target','df_'.$tName.'_errors','is_string');

        $relationsData=InstancesHelpers::GetRelationsData($this->templateId,NULL,$params);
        $formActions=InstancesHelpers::PrepareFormActions($this,$ctrl_params,$instanceId,$aeSaveInstanceMethod,$fResponseTarget,$tName,$fTagId,$relationsData,$cModule,$cMethod,$cTarget,$viewOnly,$noRedirect,$customActions);
        if(count($formActions['container'])) {
            $view->AddHtmlContent('<div class="row"><div class="col-md-12 clsBasicFormErrMsg" id="'.$fResponseTarget.'">&nbsp;</div></div>');
            foreach($formActions['container'] as $formAct) {
                $controlClassName='\NETopes\Core\Controls\\'.get_array_value($formAct,'type','Button','is_notempty_string');
                $view->AddAction((new $controlClassName($formAct['params']))->Show());
            }//END foreach
        }//if(count($formActions['container']))
        if(count($formActions['form'])) {
            $ctrl_params['actions']=$formActions['form'];
        }//if(count($formActions['form']))

        $addContentMethod='Add'.$controlClass;
        $view->$addContentMethod($ctrl_params);
        $relationsHtml=InstancesHelpers::PrepareRelationsFormPart($relationsData);
        $view->AddHtmlContent('<div class="row"><div class="col-md-12 hidden" id="df_'.$tName.'_relations">'.$relationsHtml.'</div></div>');
        $view->AddHtmlContent('<div class="row"><div class="col-md-12 hidden" id="df_'.$tName.'_custom_actions">'.json_encode($customActions).'</div></div>');
        if(count($formActions['after'])) {
            $view->AddHtmlContent('<div class="row"><div class="col-md-12 clsBasicFormErrMsg" id="'.$fResponseTarget.'">&nbsp;</div></div>');
            $afterFormActions=[];
            foreach($formActions['after'] as $formAct) {
                $controlClassName='\NETopes\Core\Controls\\'.get_array_value($formAct,'type','Button','is_notempty_string');
                $formActionsArray[]=(new $controlClassName($formAct['params']))->Show();
            }//END foreach
            $view->AddHtmlContent('<div class="row"><div class="col-md-12 actions-group">'.implode('',$afterFormActions).'</div></div>');
        }//if(count($formActions['after']))
        $view->Render();
    }//END public function ShowAddEditForm

    /**
     * @param \NETopes\Core\App\Params $params Parameters object
     * @return void
     * @throws \NETopes\Core\AppException
     */
    public function ShowAddForm(Params $params) {
        // NApp::Dlog($params,'ShowAddForm');
        $this->PrepareConfigParams($params);
        $template=DataProvider::Get('Plugins\DForms\Instances','GetTemplate',[
            'for_id'=>$this->templateId,
            'for_code'=>$this->templateCode,
            'instance_id'=>NULL,
            'for_state'=>1,
        ]);
        $this->templateId=get_array_value($template,'id',NULL,'is_integer');
        if(!$this->templateId) {
            throw new AppException('Invalid DynamicForm template!');
        }
        $noRedirect=$params->safeGet('no_redirect',FALSE,'bool');
        $target=$params->safeGet('target',$params->safeGet('_target_id','main-content','is_notempty_string'),'is_notempty_string');
        $cModule=$params->safeGet('c_module',$this->name,'is_notempty_string');
        $cMethod=$params->safeGet('c_method','Listing','is_notempty_string');
        $cTarget=$params->safeGet('c_target',$target,'is_notempty_string');
        $customActions=$params->safeGet('custom_actions',[],'is_array');

        $builder=InstancesHelpers::PrepareForm($params,$template);
        if(!$builder instanceof IControlBuilder) {
            throw new AppException('Invalid DynamicForm configuration!');
        }
        $ctrl_params=$builder->GetConfig();
        $controlClass=get_array_value($ctrl_params,'control_class','','is_string');
        $aeSaveInstanceMethod='SaveNewRecord';
        $tName=get_array_value($ctrl_params,'tname',microtime(),'is_string');
        $fTagId=get_array_value($ctrl_params,'tag_id','','is_string');

        if($controlClass!='BasicForm' && $this->actionsLocation=='form') {
            $this->actionsLocation='container';
        }
        if($this->formAsModal) {
            $view=new AppView(get_defined_vars(),$this,'modal');
            $view->SetIsModalView(TRUE);
            $view->SetModalWidth('80%');
            $view->SetTitle($template->getProperty('name','','is_string'));
        } else {
            $view=new AppView(get_defined_vars(),$this,$this->containerType);
            $view->SetTitle($template->getProperty('name','','is_string'));
        }//if($this->formAsModal)
        $fResponseTarget=get_array_value($ctrl_params,'response_target','df_'.$tName.'_errors','is_notempty_string');

        $relationsData=InstancesHelpers::GetRelationsData($this->templateId,NULL,$params);
        $formActions=InstancesHelpers::PrepareFormActions($this,$ctrl_params,NULL,$aeSaveInstanceMethod,$fResponseTarget,$tName,$fTagId,$relationsData,$cModule,$cMethod,$cTarget,FALSE,$noRedirect,$customActions);
        if(count($formActions['container'])) {
            $view->AddHtmlContent('<div class="row"><div class="col-md-12 clsBasicFormErrMsg" id="'.$fResponseTarget.'">&nbsp;</div></div>');
            foreach($formActions['container'] as $formAct) {
                $view->AddAction((new Button($formAct['params']))->Show());
            }//END foreach
        }//if(count($formActions['container']))
        if(count($formActions['form'])) {
            $ctrl_params['actions']=$formActions['form'];
        }//if(count($formActions['form']))

        $addContentMethod='Add'.$controlClass;
        $view->$addContentMethod($ctrl_params);
        $relationsHtml=InstancesHelpers::PrepareRelationsFormPart($relationsData);
        $view->AddHtmlContent('<div class="row"><div class="col-md-12 hidden" id="df_'.$tName.'_relations">'.$relationsHtml.'</div></div>');
        $view->AddHtmlContent('<div class="row"><div class="col-md-12 hidden" id="df_'.$tName.'_custom_actions">'.json_encode($customActions).'</div></div>');
        if(count($formActions['after'])) {
            $view->AddHtmlContent('<div class="row"><div class="col-md-12 clsBasicFormErrMsg" id="'.$fResponseTarget.'">&nbsp;</div></div>');
            $afterFormActions=[];
            foreach($formActions['after'] as $formAct) {
                $formActionsArray[]=(new Button($formAct['params']))->Show();
            }//END foreach
            $view->AddHtmlContent('<div class="row"><div class="col-md-12 actions-group">'.implode('',$afterFormActions).'</div></div>');
        }//if(count($formActions['after']))
        $view->Render();
    }//END public function ShowAddForm

    /**
     * @param \NETopes\Core\App\Params $params Parameters object
     * @return void
     * @throws \NETopes\Core\AppException
     */
    public function ShowEditForm(Params $params) {
        // NApp::Dlog($params->toArray(),'ShowEditForm');
        $instanceId=$params->getOrFail('id','is_not0_integer','Invalid DynamicForm instance identifier!');
        $this->PrepareConfigParams($params);
        $template=DataProvider::Get('Plugins\DForms\Instances','GetTemplate',[
            'for_id'=>$this->templateId,
            'for_code'=>NULL,
            'instance_id'=>$instanceId,
            'for_state'=>1,
        ]);
        $this->templateId=get_array_value($template,'id',NULL,'is_integer');
        if(!$this->templateId) {
            throw new AppException('Invalid DynamicForm template!');
        }

        $viewOnly=$params->safeGet('view_only',FALSE,'bool');
        $noRedirect=$params->safeGet('no_redirect',FALSE,'bool');
        $target=$params->safeGet('target',$params->safeGet('_target_id','main-content','is_notempty_string'),'is_notempty_string');
        $cModule=$params->safeGet('c_module',$this->name,'is_notempty_string');
        $cMethod=$params->safeGet('c_method','Listing','is_notempty_string');
        $cTarget=$params->safeGet('c_target',$target,'is_notempty_string');
        $customActions=$params->safeGet('custom_actions',[],'is_array');

        $builder=InstancesHelpers::PrepareForm($params,$template,$instanceId);
        if(!$builder instanceof IControlBuilder) {
            throw new AppException('Invalid DynamicForm configuration!');
        }
        $ctrl_params=$builder->GetConfig();
        $controlClass=get_array_value($ctrl_params,'control_class','','is_string');
        $aeSaveInstanceMethod='SaveRecord';
        $tName=get_array_value($ctrl_params,'tname',microtime(),'is_string');
        $fTagId=get_array_value($ctrl_params,'tag_id','','is_string');

        if($controlClass!='BasicForm' && $this->actionsLocation=='form') {
            $this->actionsLocation='container';
        }
        if($this->formAsModal) {
            $view=new AppView(get_defined_vars(),$this,'modal');
            $view->SetIsModalView(TRUE);
            $view->SetModalWidth('80%');
            $view->SetTitle($template->getProperty('name','','is_string'));
        } else {
            $view=new AppView(get_defined_vars(),$this,$this->containerType);
            $view->SetTitle($template->getProperty('name','','is_string'));
        }//if($this->formAsModal)
        $fResponseTarget=get_array_value($ctrl_params,'response_target','df_'.$tName.'_errors','is_notempty_string');

        $relationsData=InstancesHelpers::GetRelationsData($this->templateId,NULL,$params);
        $formActions=InstancesHelpers::PrepareFormActions($this,$ctrl_params,$instanceId,$aeSaveInstanceMethod,$fResponseTarget,$tName,$fTagId,$relationsData,$cModule,$cMethod,$cTarget,$viewOnly,$noRedirect,$customActions);
        if(count($formActions['container'])) {
            $view->AddHtmlContent('<div class="row"><div class="col-md-12 clsBasicFormErrMsg" id="'.$fResponseTarget.'">&nbsp;</div></div>');
            foreach($formActions['container'] as $formAct) {
                $actionClass='\NETopes\Core\Controls\\'.get_array_value($formAct,'type','Button','is_notempty_string');
                if(class_exists($actionClass)) {
                    $view->AddAction((new $actionClass($formAct['params']))->Show());
                }
            }//END foreach
        }//if(count($formActions['container']))
        if(count($formActions['form'])) {
            $ctrl_params['actions']=$formActions['form'];
        }//if(count($formActions['form']))

        $addContentMethod='Add'.$controlClass;
        $view->$addContentMethod($ctrl_params);
        $relationsHtml=InstancesHelpers::PrepareRelationsFormPart($relationsData);
        $view->AddHtmlContent('<div class="row"><div class="col-md-12 hidden" id="df_'.$tName.'_relations">'.$relationsHtml.'</div></div>');
        $view->AddHtmlContent('<div class="row"><div class="col-md-12 hidden" id="df_'.$tName.'_custom_actions">'.json_encode($customActions).'</div></div>');
        if(count($formActions['after'])) {
            $view->AddHtmlContent('<div class="row"><div class="col-md-12 clsBasicFormErrMsg" id="'.$fResponseTarget.'">&nbsp;</div></div>');
            $afterFormActions=[];
            foreach($formActions['after'] as $formAct) {
                $actionClass='\NETopes\Core\Controls\\'.get_array_value($formAct,'type','Button','is_notempty_string');
                if(class_exists($actionClass)) {
                    $formActionsArray[]=(new $actionClass($formAct['params']))->Show();
                }
            }//END foreach
            $view->AddHtmlContent('<div class="row"><div class="col-md-12 actions-group">'.implode('',$afterFormActions).'</div></div>');
        }//if(count($formActions['after']))
        $view->Render();
    }//END public function ShowEditForm

    /**
     * @param \NETopes\Core\App\Params $params Parameters object
     * @return void
     * @throws \NETopes\Core\AppException
     */
    public function ShowViewForm(Params $params) {
        // NApp::Dlog($params->toArray(),'ShowViewForm');
        $viewAsModal=$params->safeGet('view_as_modal',$this->viewAsModal,'bool');
        $params->set('forms_as_modal',intval($viewAsModal));
        $params->set('view_only',1);
        $this->ShowEditForm($params);
    }//END public function ShowViewForm

    /**
     * @param \NETopes\Core\App\Params $params Parameters object
     * @return void
     * @throws \NETopes\Core\AppException
     */
    public function SaveInstance(Params $params) {
        // NApp::Dlog($params->toArray(),'SaveInstance');
        $instanceId=$params->safeGet('id',NULL,'is_integer');
        $formId=$params->safeGet('form_id','','is_string');
        $this->PrepareConfigParams($params);
        $check=InstancesHelpers::ValidateSaveParams($params);
        if(!$check) {
            $this->ShowAddEditErrors($params,$formId);
            return;
        }//if(!$check)
        $this->templateId=$params->safeGet('id_template',$this->templateId,'is_not0_integer');
        $aParams=clone $params;
        $aParams->set('no_check',TRUE);
        $aParams->set('no_redirect',TRUE);
        if($instanceId>0) {
            $this->Exec('SaveRecord',$aParams);
        } else {
            $this->Exec('SaveNewRecord',$aParams);
        }//if($instanceId>0)
        InstancesHelpers::RedirectAfterAction($params,$this);
    }//END public function SaveInstance

    /**
     * @param \NETopes\Core\App\Params $params Parameters object
     * @return void
     * @throws \NETopes\Core\AppException
     */
    public function SaveNewRecord(Params $params) {
        // NApp::Dlog($params,'SaveNewRecord');
        if(!$params->safeGet('no_check',FALSE,'bool')) {
            $this->PrepareConfigParams($params);
            $formId=$params->safeGet('form_id','','is_string');
            $check=InstancesHelpers::ValidateSaveParams($params);
            if(!$check) {
                $this->ShowAddEditErrors($params,$formId);
                return;
            }//if(!$check)
        }//if(!$params->safeGet('no_check',FALSE,'bool'))
        $template=DataProvider::Get('Plugins\DForms\Instances','GetTemplate',['for_id'=>$this->templateId]);
        if(!$template instanceof IEntity) {
            throw new AppException('Invalid DynamicForm template!');
        }
        $fieldsData=$params->safeGet('df_fields_values');
        $relationsData=$params->safeGet('df_relations_values');

        $transaction=AppSession::GetNewUID($template->getProperty('code',$this->templateId,'is_notempty_string'));
        DataProvider::StartTransaction('Plugins\DForms\Instances',$transaction);
        try {
            $dbResult=DataProvider::GetArray('Plugins\DForms\Instances','SetNewInstance',[
                'template_id'=>$this->templateId,
                'user_id'=>NApp::GetCurrentUserId(),
            ],['transaction'=>$transaction]);
            $instanceId=get_array_value($dbResult,[0,'inserted_id'],0,'is_integer');
            if($instanceId<=0) {
                NApp::Dlog($dbResult,'SetNewInstance>>$dbResult');
                throw new AppException('Database error on instance insert!');
            }
            /** @var \NETopes\Core\Data\VirtualEntity $f */
            foreach($fieldsData as $f) {
                $fieldValue=$f->getProperty('value');
                if(($f->getProperty('itype',0,'is_integer')==2 || $f->getProperty('parent_itype',0,'is_integer')==2) && is_array($fieldValue)) {
                    foreach($fieldValue as $index=>$fValue) {
                        $dbResult=DataProvider::GetArray('Plugins\DForms\Instances','SetNewInstanceValue',[
                            'instance_id'=>$instanceId,
                            'item_uid'=>$f->getProperty('uid',NULL,'is_notempty_string'),
                            'in_value'=>$fValue,
                            'in_name'=>NULL,
                            'in_index'=>$index,
                        ],['transaction'=>$transaction]);
                        if(get_array_value($dbResult,[0,'inserted_id'],0,'is_integer')<=0) {
                            NApp::Dlog($dbResult,'SetNewInstanceValue>>$dbResult');
                            throw new AppException('Database error on instance value insert!');
                        }
                    }//END foreach
                } else {
                    $dbResult=DataProvider::GetArray('Plugins\DForms\Instances','SetNewInstanceValue',[
                        'instance_id'=>$instanceId,
                        'item_uid'=>$f->getProperty('uid',NULL,'is_notempty_string'),
                        'in_value'=>$fieldValue,
                        'in_name'=>NULL,
                        'in_index'=>NULL,
                    ],['transaction'=>$transaction]);
                    if(get_array_value($dbResult,[0,'inserted_id'],0,'is_integer')<=0) {
                        NApp::Dlog($dbResult,'SetNewInstanceValue>>$dbResult');
                        throw new AppException('Database error on instance value insert!');
                    }
                }//if($field['itype']==2 || $field['parent_itype']==2 && is_array($field['value']))
            }//END foreach
            /** @var \NETopes\Core\Data\VirtualEntity $r */
            foreach($relationsData as $r) {
                $dbResult=DataProvider::GetArray('Plugins\DForms\Instances','SetNewInstanceRelation',[
                    'instance_id'=>$instanceId,
                    'relation_id'=>$r->getProperty('id',NULL,'is_integer'),
                    'in_ivalue'=>$r->getProperty('ivalue',NULL,'?is_integer'),
                    'in_svalue'=>$r->getProperty('svalue',NULL,'?is_string'),
                ],['transaction'=>$transaction]);
                if(get_array_value($dbResult,[0,'inserted_id'],0,'is_integer')<=0) {
                    NApp::Dlog($dbResult,'SetNewInstanceRelation>>$dbResult');
                    throw new AppException('Database error on instance value insert!');
                }
            }//END foreach

            $dbResult=DataProvider::GetArray('Plugins\DForms\Instances','SetInstanceUid',['for_id'=>$instanceId],['transaction'=>$transaction]);
            DataProvider::CloseTransaction('Plugins\DForms\Instances',$transaction,FALSE);
        } catch(AppException $e) {
            DataProvider::CloseTransaction('Plugins\DForms\Instances',$transaction,TRUE);
            NApp::Elog($e->getMessage());
            throw $e;
        }//END try
        InstancesHelpers::RedirectAfterAction($params,$this);
    }//END public function SaveNewRecord

    /**
     * @param \NETopes\Core\App\Params $params Parameters object
     * @return void
     * @throws \NETopes\Core\AppException
     */
    public function SaveRecord(Params $params) {
        // NApp::Dlog($params,'SaveRecord');
        $instanceId=$params->safeGet('id',NULL,'is_not0_integer');
        if(!$params->safeGet('no_check',FALSE,'bool')) {
            $this->PrepareConfigParams($params);
            $formId=$params->safeGet('form_id','','is_string');
            $check=InstancesHelpers::ValidateSaveParams($params);
            if(!$check) {
                $this->ShowAddEditErrors($params,$formId);
                return;
            }//if(!$check)
        }//if(!$params->safeGet('no_check',FALSE,'bool'))
        $template=DataProvider::Get('Plugins\DForms\Instances','GetTemplate',['for_id'=>$this->templateId]);
        if(!is_object($template)) {
            throw new AppException('Invalid DynamicForm template!');
        }
        $fieldsData=$params->safeGet('df_fields_values');
        // $relationsData=$params->safeGet('df_relations_values');

        $transaction=AppSession::GetNewUID($template->getProperty('code',$template,'is_notempty_string'));
        DataProvider::StartTransaction('Plugins\DForms\Instances',$transaction);
        try {
            $result=DataProvider::Get('Plugins\DForms\Instances','UnsetInstanceValues',['for_id'=>$instanceId],['transaction'=>$transaction]);
            if($result===FALSE) {
                throw new AppException('Database error on instance update!');
            }
            /** @var \NETopes\Core\Data\VirtualEntity $f */
            foreach($fieldsData as $f) {
                $fieldValue=$f->getProperty('value');
                if(($f->getProperty('itype',0,'is_integer')==2 || $f->getProperty('parent_itype',0,'is_integer')==2) && is_array($fieldValue)) {
                    foreach($fieldValue as $index=>$fValue) {
                        $dbResult=DataProvider::GetArray('Plugins\DForms\Instances','SetNewInstanceValue',[
                            'instance_id'=>$instanceId,
                            'item_uid'=>$f->getProperty('uid',NULL,'is_notempty_string'),
                            'in_value'=>$fValue,
                            'in_name'=>NULL,
                            'in_index'=>$index,
                        ],['transaction'=>$transaction]);
                        if(get_array_value($dbResult,[0,'inserted_id'],0,'is_integer')<=0) {
                            NApp::Dlog($dbResult,'SetNewInstanceValue>>$dbResult');
                            throw new AppException('Database error on instance value insert!');
                        }
                    }//END foreach
                } else {
                    $dbResult=DataProvider::GetArray('Plugins\DForms\Instances','SetNewInstanceValue',[
                        'instance_id'=>$instanceId,
                        'item_uid'=>$f->getProperty('uid',NULL,'is_notempty_string'),
                        'in_value'=>$fieldValue,
                        'in_name'=>NULL,
                        'in_index'=>NULL,
                    ],['transaction'=>$transaction]);
                    if(get_array_value($dbResult,[0,'inserted_id'],0,'is_integer')<=0) {
                        NApp::Dlog($dbResult,'SetNewInstanceValue>>$dbResult');
                        throw new AppException('Database error on instance value insert!');
                    }
                }//if($field['itype']==2 || $field['parent_itype']==2 && is_array($field['value']))
            }//END foreach

            DataProvider::Get('Plugins\DForms\Instances','SetInstanceState',[
                'for_id'=>$instanceId,
                'user_id'=>NApp::GetCurrentUserId(),
            ],['transaction'=>$transaction]);

            DataProvider::CloseTransaction('Plugins\DForms\Instances',$transaction,FALSE);
        } catch(AppException $e) {
            DataProvider::CloseTransaction('Plugins\DForms\Instances',$transaction,TRUE);
            NApp::Elog($e->getMessage());
            throw $e;
        }//END try
        InstancesHelpers::RedirectAfterAction($params,$this);
    }//END public function SaveRecord

    /**
     * @param \NETopes\Core\App\Params $params Parameters object
     * @return void
     * @throws \NETopes\Core\AppException
     */
    public function DeleteRecord(Params $params) {
        $id=$params->getOrFail('id','is_not0_integer','Invalid record identifier!');
        $this->PrepareConfigParams($params);
        $result=DataProvider::Get('Plugins\DForms\Instances','UnsetInstance',['for_id'=>$id]);
        if($result===FALSE) {
            throw new AppException('Unknown database error!');
        }
        $cModule=$params->safeGet('c_module',get_called_class(),'is_notempty_string');
        $cMethod=$params->safeGet('c_method','Listing','is_notempty_string');
        $cTarget=$params->safeGet('c_target','main-content','is_notempty_string');
        $params->remove('id');
        $params->remove('c_module');
        $params->remove('c_method');
        $params->remove('c_target');
        $params->set('id_template',$this->templateId);
        $params->set('target',$cTarget);
        ModulesProvider::Exec($cModule,$cMethod,$params,$cTarget);
    }//END public function DeleteRecord

    /**
     * @param \NETopes\Core\App\Params $params Parameters object
     * @return void
     * @throws \NETopes\Core\AppException
     */
    public function EditRecordState(Params $params) {
        $id=$params->getOrFail('id','is_not0_integer','Invalid DynamicForm instance identifier!');
        $result=DataProvider::Get('Plugins\DForms\Instances','SetInstanceState',[
            'for_id'=>$id,
            'in_state'=>$params->safeGet('state',NULL,'is_integer'),
            'user_id'=>NApp::GetCurrentUserId(),
        ]);
        if($result===FALSE) {
            throw new AppException('Failed database operation!');
        }
    }//END public function EditRecordState

    /**
     * @param \NETopes\Core\App\Params $params Parameters object
     * @return InstancesPrintContentBuilder
     * @throws \NETopes\Core\AppException
     */
    public function GetPrintContentBuilder(Params $params): InstancesPrintContentBuilder {
        $instanceId=$params->getOrFail('id','is_not0_integer','Invalid DynamicForm instance identifier!');
        $instance=DataProvider::Get('Plugins\DForms\Instances','GetInstanceItem',['for_id'=>$instanceId]);
        if(!$instance instanceof IEntity) {
            throw new AppException('Invalid DynamicForm instance object!');
        }
        return new InstancesPrintContentBuilder($instance);
    }//END public function GetPrintContentBuilder

    /**
     * @param \NETopes\Core\App\Params $params Parameters object
     * @return void
     * @throws \NETopes\Core\AppException
     */
    public function PrintInstance(Params $params) {
        // NApp::Dlog($params,'PrintInstance');
        $contentBuilder=$this->GetPrintContentBuilder($params);
        if(!$contentBuilder instanceof InstancesPrintContentBuilder) {
            throw new AppException('Invalid instance content builder object!');
        }
        $contentBuilder->PrepareContent();
        $pdfBuilder=new PdfBuilder(['orientation'=>$contentBuilder->pageOrientation]);
        $pdfBuilder->SetTitle($contentBuilder->documentTitle);
        $pdfBuilder->AddContents(explode('[[insert_new_page]]',$contentBuilder->content));
        $pdfBuilder->Render();
        // echo $contentBuilder->content;
    }//END public function PrintInstance

    /**
     * @param \NETopes\Core\App\Params $params Parameters object
     * @return void
     * @throws \NETopes\Core\AppException
     */
    public function GetFormDataForPrint(Params $params) {
        // NApp::Dlog($params,'GetFormDataForPrint');
        $instanceId=$this->GetInstanceId($params);
        $params->set('id',$instanceId);
        $contentBuilder=$this->GetPrintContentBuilder($params);
        if(!$contentBuilder instanceof InstancesPrintContentBuilder) {
            throw new AppException('Invalid instance content builder object!');
        }
        $contentBuilder->PrepareContent();
        return $contentBuilder->content;
    }//END public function GetFormDataForPrint
}//END class Instances extends Module