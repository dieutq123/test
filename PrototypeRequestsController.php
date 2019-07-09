<?php
namespace App\Controller;
use Exception;
use App\Controller\AppController;
use App\Utils\AppUtility;
use App\Library\Constant;
use App\View\Helper\DateHelper;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;
use Cake\I18n\Time;
use PHPExcel_IOFactory;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Border;
use PHPExcel_Style_Fill;
use PHPExcel_Settings;
/**
 * PrototypeRequests Controller
 *
 * @property \App\Model\Table\SsiProtypeRequestsTable $SsiPrototypeRequests
 * @property \App\Model\Table\SsiEstimateMaterialsInfosTable $SsiEstimateMaterialsInfos
 * @property \App\Model\Table\SsiProtypePrescriptionResultsTable $SsiProtypePrescriptionResults
 * @property \App\Model\Table\SsiProtypeResultInfosTable $SsiProtypeResultInfos
 * @property \App\Model\Table\SsiEstimatePkgInfosTable $SsiEstimatePkgInfos
 *
 * @method \App\Model\Entity\SsiPrototypeRequests[] paginate($object = null, array $settings = [])
 */
class PrototypeRequestsController extends AppController
{
    protected $date;

    public function initialize()
    {
        parent::initialize();
        $this->loadModel('QuotationInfos');
        $this->loadModel('SsiEstimatePkgInfos');
        $this->loadModel('CaseManagements');
        $this->loadModel('SsiPrototypeRequests');
        $this->loadModel('SsiProtypeComment');
        $this->loadModel('SsiEstimateMaterialsInfos');
        $this->loadModel('SsiProtypeResultInfos');
        $this->loadModel('SsiProtypePrescriptions');
        $this->loadModel('SsiProtypePrescriptionResults');
        $this->loadModel('SsiCustomerInfos');
        $this->loadModel('SsiItemNames');
        $this->loadModel('SsiPrototypeRequestPkgInfos');
        $this->loadModel('SsiPrototypeResultPkgInfos');
        $this->loadModel('SsiEstimateFunctionalities');
        $this->loadModel('SsiEstimateCoatings');
        $this->loadModel('SsiEstimateCoatingColors');
        $this->loadModel('SsiUsers');
        $this->loadModel('SsiOrganizations');
        $this->loadModel('SsiProtypeCommentReaders');
        $this->loadModel('SsiPrototypeConfirmHistories');
        $this->loadModel('SsiPrototypeConfirmRequestHistories');
        $this->loadModel('SsiEstimateDestinationCountries');
        $this->loadModel('SsiPrototypeRequestBillings');
        $this->loadModel('SsiPrototypeRequestShippings');
        $this->date = new DateHelper(new \Cake\View\View()); //call helper
    }

    public function index()
    {
        $sortkey = $this->request->getQuery('sortkey');
        $sortFlg = $this->request->getQuery('sortFlg');

        $sort_colums = "SsiPrototypeRequests.prototype_request_name";
        $sort_type = 'DESC';

        $sortArray = array(
            'prototype_request_name'    => 'SsiPrototypeRequests.prototype_request_name',
            'item_name'                 => 'item_name',
            'shipment_name'             => 'SsiPrototypeRequests.shipment_name',
            'shipping_name'             => 'shipping_name',
            'company_name'              => 'company_name',
            'shape'                     => 'shape',
            'pack_spec'                 => 'pack_spec',
            'user_name'                 => 'user_name',
            'case_est_proptotype_no'    => "CONCAT( LPAD(SsiPrototypeRequests.case_management_no, 6, '0'),'-',LPAD(SsiPrototypeRequests.est_branch_no, 3, '0'),'-',LPAD(SsiPrototypeRequests.prototype_request_branch_no, 3, '0'))",
            'created'                   => 'SsiPrototypeRequests.created',
            'modified'                  => 'SsiPrototypeRequests.modified',
            'created_usrcd_name'        => 'SsiUsers1.user_name',
            'modified_usrcd_name'       => 'SsiUsers2.user_name',
        );

        if (!empty($sortkey)) {
            $sort_colums = $sortArray[$sortkey];
        }

        if ($sortFlg == 1) {
            $sort_type = 'ASC';
        }else {
            $sort_type = 'DESC';
        }

        $searchCondition = [];
        $showcnt =  isset($this->request->query['showcnt']) ? $this->request->query['showcnt'] : 10;
        $page    = isset($this->request->query['page']) ? $this->request->query['page'] : 1;
        $searchCondition['prototype_request_name'] = isset($this->request->query['prototype_request_name']) ? trim($this->request->query['prototype_request_name']) : '';
        $searchCondition['item_name'] = isset($this->request->query['item_name']) ? trim($this->request->query['item_name']) : '';
        $searchCondition['shipment_name'] = isset($this->request->query['shipment_name']) ? trim($this->request->query['shipment_name']) : '';
        $searchCondition['cl_name'] = isset($this->request->query['cl_name']) ? trim($this->request->query['cl_name']) : '';
        $searchCondition['agent_type'] = isset($this->request->query['agent_type']) ? trim($this->request->query['agent_type']) : '';
        $searchCondition['sale_staff'] = isset($this->request->query['sale_staff']) ? trim($this->request->query['sale_staff']) : '';
        $searchCondition['case_est_proptotype_no'] = isset($this->request->query['case_est_proptotype_no']) ? trim($this->request->query['case_est_proptotype_no']) : null;

        $user = $this->Auth->user();
        $user_login_name = $user['user_name'];
        $searchCondition['create_modified_name'] = isset($this->request->query['create_modified_name']) ? $this->request->query['create_modified_name'] : $user_login_name;

        $offset = ($page - 1) * $showcnt;
        $query = $this->SsiPrototypeRequests->getAllProtypeRequest();
        $query->contain(['SsiUsers1'])->contain(['SsiUsers2']);
        if ($searchCondition['prototype_request_name'] != '') {
            $query->where(["SsiPrototypeRequests.prototype_request_name like " =>"%".AppUtility::escapeLike($searchCondition['prototype_request_name'])."%"]);
        }
        if ($searchCondition['item_name'] != '') {
            $query->having(["item_name like" => '%'.AppUtility::escapeLike($searchCondition['item_name']).'%']);
        }
        if ($searchCondition['shipment_name'] != '') {
             $query->having(['OR' =>["shipment_name like " =>"%".AppUtility::escapeLike($searchCondition['shipment_name'])."%",
                 " shipping_name like " =>"%".AppUtility::escapeLike($searchCondition['shipment_name'])."%"]]);
        }
        if ($searchCondition['cl_name'] != '') {
             $query->where(["Companys.company_name like " =>"%".AppUtility::escapeLike($searchCondition['cl_name'])."%"]);
        }
        if ($searchCondition['agent_type'] != '') {
             $query->where(["SsiItemName.item_name like " =>"%".AppUtility::escapeLike($searchCondition['agent_type'])."%"]);
        }
        if ($searchCondition['sale_staff'] != '') {
             $query->where(["ssi_users.user_name like " =>"%".AppUtility::escapeLike($searchCondition['sale_staff'])."%"]);
        }
        if (!empty($searchCondition['case_est_proptotype_no'])) {
            $query->having(['case_est_proptotype_no like' => '%'.$searchCondition['case_est_proptotype_no'].'%']);
        }
        if (!empty($searchCondition['create_modified_name'])) {
            $query->where([
                "OR" => [
                    ["SsiUsers1.user_name like " => "%".$searchCondition['create_modified_name']."%"],
                    ["SsiUsers2.user_name like " => "%".$searchCondition['create_modified_name']."%"],
                    ["ssi_users.user_name like " =>"%".$searchCondition['create_modified_name']."%"]
                ]
            ]);
        }
        $sort = array();
        $sort[$sort_colums] = $sort_type;

        $totalcount = $query->count();
        //画面表示件数
         // $query->order($sort)->limit($showcnt)->offset($offset);
        $prototypeRequests = $query->order($sort)->limit($showcnt)->offset($offset)->toArray();

        foreach ($prototypeRequests as $prototypeRequest) {
            $prototypeRequest['quotationInfo'] = $this->QuotationInfos->find('all', [
                'conditions' => ['QuotationInfos.case_management_no = ' . $prototypeRequest->case_management_no,
                    'QuotationInfos.est_branch_no = ' . $prototypeRequest->est_branch_no]
            ])->first();
        }

        if($totalcount){
            $total_show = count($prototypeRequests) + $offset;
        }else{
            $total_show = 0;
        }
        $this->set(compact('prototypeRequests',
            'total_show','statusVal',
            'totalcount',
            'showcnt',
            'searchno',
            'offset',
            'page',
            'sortkey',
            'sortFlg',
            'searchCondition'));
        $this->set('_serialize', ['prototypeRequests']);
    }

    public function view($id = null)
    {
        $data = $this->getData(null, $id);
        $isView = true;

        $this->set(compact('data', 'isView'));
        $this->set('_serialize', ['data', 'isView']);
    }

    private function getData($quotationId = null, $prototypeId = null, $copy = false) {
        $result = array();
        // Ssi Estimate Prototype Request
        $result['ssi_prototype_request'] = $this->SsiPrototypeRequests->getPrototype($prototypeId);
        $result['ssi_prototype_request_billings'] = $this->SsiPrototypeRequestBillings->getPrototypeRequestBillingByPrototypeId($prototypeId);
        if(empty($result['ssi_prototype_request_billings'])){
            $result['ssi_prototype_request_billing_new'] = $this->SsiPrototypeRequestBillings->newEntity();
        }
        // Comment
        $result['ssi_prototype_request_shippings'] = $this->SsiPrototypeRequestShippings->getPrototypeRequestShippingsByPrototypeId($prototypeId);
        if(empty($result['ssi_prototype_request_shippings'])){
            $result['ssi_prototype_request_shippings_new'] = $this->SsiPrototypeRequestShippings->newEntity();
        }
        $comments = $this->SsiProtypeComment->getComments($result['ssi_prototype_request']->case_management_no, $result['ssi_prototype_request']->est_branch_no, $result['ssi_prototype_request']->prototype_request_branch_no);

        $result['getMaxPagedComments'] = $this->SsiProtypeComment->getMaxPagedComments($result['ssi_prototype_request']->case_management_no, $result['ssi_prototype_request']->est_branch_no, $result['ssi_prototype_request']->prototype_request_branch_no);

        $user = $this->Auth->user();
        $user_login = $user['user_cd'];
        $authorCommment = array();
        foreach ($comments as &$comment) {
            $cst_cd = $comment->modified_usrcd;
            $authorCommment[$cst_cd] = $this->SsiUsers->getSsiUserInfo($cst_cd);

            // check_comment_reader
            $countCommentReader = $this->SsiProtypeCommentReaders->find()
                ->where([
                    'comment_id' => $comment->id,
                    'created_usrcd' => $user_login
                ])
                ->count();

            $comment['reader_comment'] = $countCommentReader > 0;
        }
        $result['authorCommment'] = $authorCommment;

        $result['comments'] = $comments;

        if (!empty($result['ssi_prototype_request']->analysis_subcontractor)) {
            $customer = $this->SsiCustomerInfos->find('all')
                ->where([
                    'customercd' => $result['ssi_prototype_request']->analysis_subcontractor
                ])
                ->first();

            $result['ssi_prototype_request']->analysis_subcontractor_name = '';

            if (!empty($customer->customercd)) {
                $result['ssi_prototype_request']->analysis_subcontractor_name .= $customer->customercd . ' ';
            }

            if (!empty($customer->branch_name)) {
                $result['ssi_prototype_request']->analysis_subcontractor_name .= $customer->branch_name;
            }
        }

        if (!empty($result['ssi_prototype_request']->prototype_complete_date)){
            $result['ssi_prototype_request']->prototype_complete_date = date('Y/m/d', strtotime($result['ssi_prototype_request']->prototype_complete_date));
        }

        if (!empty($result['ssi_prototype_request']->act_plan_date)){
            $result['ssi_prototype_request']->act_plan_date = date('Y/m/d', strtotime($result['ssi_prototype_request']->act_plan_date));
        }

        if (!empty($result['ssi_prototype_request']->self_by_date)){
            $result['ssi_prototype_request']->self_by_date = date('Y/m/d', strtotime($result['ssi_prototype_request']->self_by_date));
        }

        // get case management no and est branch no
        $case_management_no = null;
        $est_branch_no = null;

        if(!empty($result['ssi_prototype_request']->case_management_no) && !empty($result['ssi_prototype_request']->est_branch_no)) {
            $case_management_no = $result['ssi_prototype_request']->case_management_no;
            $est_branch_no = $result['ssi_prototype_request']->est_branch_no;
        }

        // QuotationInfos
        $result['quotation_info'] = $this->QuotationInfos->getQuotationInfo($quotationId, $case_management_no, $est_branch_no);

        $result['SsiEstimateDestinationCountries'] = $this->SsiEstimateDestinationCountries->getDestinationCountries($result['quotation_info']->id);

        $result['quotation_info']['est_no'] = sprintf("%06d", $result['quotation_info']->case_management_no).
            '-'.sprintf("%03d", $result['quotation_info']->est_branch_no).
            '-'.sprintf("%03d", $result['quotation_info']->prototype_request_branch_no).
            '-'.sprintf("%03d", $result['quotation_info']->prototype_result_branch_no);

        $result['ssi_estimate_functionalities'] = $this->SsiEstimateFunctionalities->getFunctionalities($result['quotation_info']->id);
        $result['ssi_estimate_coating_colors'] =  $this->SsiEstimateCoatingColors->getCoatingColor($result['quotation_info']->id);
        $result['ssi_estimate_coatings'] =  $this->SsiEstimateCoatings->getCoatings($result['quotation_info']->id);

        //get prototype no
        $prototypeNo = null;
        if (empty($prototypeId)) {
            //get max branch no
            $prototypeMaxBranchNo = $this->SsiPrototypeRequests->find('all')
                ->where(['case_management_no' => $result['quotation_info']->case_management_no])
                ->where(['est_branch_no' => $result['quotation_info']->est_branch_no])
                ->max('prototype_request_branch_no');

            $result['ssi_prototype_request']->case_management_no = $result['quotation_info']->case_management_no;
            $result['ssi_prototype_request']->est_branch_no = $result['quotation_info']->est_branch_no;
            $result['ssi_prototype_request']->prototype_request_branch_no = empty($prototypeMaxBranchNo) ? 1 : $prototypeMaxBranchNo->prototype_request_branch_no + 1;

            $result['ssi_prototype_request']->prototype_no = sprintf("%06d", $result['ssi_prototype_request']->case_management_no).
                '-'.sprintf("%03d", $result['ssi_prototype_request']->est_branch_no).
                '-'.sprintf("%03d", $result['ssi_prototype_request']->prototype_request_branch_no);

            // get prototype prescription
            $result['prototype_prescriptions'] = $this->getPrototypePrescriptionsNew($result['quotation_info']);

            // init prototype pkg info
            $result['prototype_pkg_info'] = $this->getPrototypePkgInfoNew($result['quotation_info']);


        } else {
            // get prototype prescription
            $result['prototype_prescriptions'] = $this->getPrototypePrescriptions($result['ssi_prototype_request']);

            // init prototype pkg info
            $result['prototype_pkg_info'] = $this->getPrototypePkgInfo($result['ssi_prototype_request']);

            if($copy){
                $prototypeMaxBranchNo = $this->SsiPrototypeRequests->find('all')
                    ->where(['case_management_no' => $result['quotation_info']->case_management_no])
                    ->where(['est_branch_no' => $result['quotation_info']->est_branch_no])
                    ->max('prototype_request_branch_no');
                $result['ssi_prototype_request']->prototype_request_branch_no = empty($prototypeMaxBranchNo) ? 1 : $prototypeMaxBranchNo->prototype_request_branch_no + 1;

                $result['ssi_prototype_request']->prototype_no = sprintf("%06d", $result['ssi_prototype_request']->case_management_no).
                    '-'.sprintf("%03d", $result['ssi_prototype_request']->est_branch_no).
                    '-'.sprintf("%03d", $result['ssi_prototype_request']->prototype_request_branch_no);
            }else{
                $result['ssi_prototype_request']->prototype_no = sprintf("%06d", $result['ssi_prototype_request']->case_management_no).
                    '-'.sprintf("%03d", $result['ssi_prototype_request']->est_branch_no).
                    '-'.sprintf("%03d", $result['ssi_prototype_request']->prototype_request_branch_no);
            }
            $result['ssi_prototype_request']->functionId = "";

            $ssiConfirmHistories = $this->SsiPrototypeConfirmHistories->getLastUserConfirm($prototypeId);
            $result['prototype_confirm_user'] = $ssiConfirmHistories;

            $ssiConfirmRequestHistories = $this->SsiPrototypeConfirmRequestHistories->getLastUserConfirm($prototypeId);
            $result['prototype_confirm_request_user'] = $ssiConfirmRequestHistories;

        }

        // Case Management
        if(empty($copy)) {
            $result['ssi_prototype_request']->functionId = Configure::read('FOLDER_PRE_NAME')['prototype'].$result['ssi_prototype_request']->case_management_no.$result['ssi_prototype_request']->est_branch_no.$result['ssi_prototype_request']->prototype_request_branch_no;
        }

        $result['case_management'] = $this->CaseManagements->getCaseManagement($result['quotation_info']->case_management_no);
        // ssi estimate prototype result
        $result['ssi_prototype_result_info'] = [1 => null, 2 => null, 3 => null, 4 => null, 5 => null];

        $ssiProtypeResultInfos = $this->SsiProtypeResultInfos->getResultInfo($result);

        if (count($ssiProtypeResultInfos) == 0) {
            $prototype_result_info = $this->SsiProtypeResultInfos->newEntity();
            $prototype_result_info->prototype_result_branch_no = 1;
            $ssiProtypeResultInfos = array(
                $prototype_result_info
            );
        }

        foreach ($ssiProtypeResultInfos as &$value) {
            if ($value->veryfied_delivdate) {
                $value->veryfied_delivdate = date('Y/m/d', strtotime($value->veryfied_delivdate));
            }

            if ($value->protoype_complete_planted) {
                $value->protoype_complete_planted = date('Y/m/d', strtotime($value->protoype_complete_planted));
            }

            if ($value->formulation_completed_date) {
                $value->formulation_completed_date = $this->date->convertDateToString($value->formulation_completed_date);
            }

            if ($value->pkg_complete_plan_date) {
                $value->pkg_complete_plan_date = $this->date->convertDateToString($value->pkg_complete_plan_date);
            }

            if ($value->pkg_completed_date) {
                $value->pkg_completed_date = $this->date->convertDateToString($value->pkg_completed_date);
            }

            $value->count_file_A01 = AppUtility::getExitFilesCount('SR0301P' . $value->prototype_result_branch_no, 'A01', $result['ssi_prototype_request']->functionId);
            $value->count_file_A02 = AppUtility::getExitFilesCount('SR0301P' . $value->prototype_result_branch_no, 'A02', $result['ssi_prototype_request']->functionId);

            $result['ssi_prototype_result_info'][$value->prototype_result_branch_no] = $value;
        }
        //ssi prototype prescription result
        $result['ssi_prototype_prescription_results'] = $this->getPrototypePrescriptionResult($result['ssi_prototype_request']);

        //SsiPrototypeResultPkgInfosTable
        $result['ssi_prototype_result_pkg_infos'] = $this->getPrototypeResultPkgInfos($result['ssi_prototype_request']);

        //customer contact
        $result['customer_contact'] = null;

        // set default companyCd and branchCd
        if (empty($prototypeId)) {
            $result['ssi_prototype_request']['contact_company_cd']  = $result['case_management']['company_cd'];
            $result['ssi_prototype_request']['bill_company_cd']     = $result['case_management']['company_cd'];
            $result['ssi_prototype_request']['delivery_company_cd'] = $result['case_management']['company_cd'];

            $result['ssi_prototype_request']['contact_branch_cd']  = $result['case_management']['branch_cd'];
            $result['ssi_prototype_request']['bill_branch_cd']     = $result['case_management']['branch_cd'];
            $result['ssi_prototype_request']['delivery_branch_cd'] = $result['case_management']['branch_cd'];
        }

        if (!empty($result['ssi_prototype_request']['contact_company_cd']) && !empty($result['ssi_prototype_request']['contact_branch_cd'])) {
            $result['customer_contact'] = $this->SsiCustomerInfos->getCompany($result['ssi_prototype_request']['contact_company_cd'], $result['ssi_prototype_request']['contact_branch_cd']);
        }

        //customer bill
        $result['customer_bill'] = null;

        if (!empty($result['ssi_prototype_request']['bill_company_cd']) && !empty($result['ssi_prototype_request']['bill_branch_cd'])) {
            $result['customer_bill'] = $this->SsiCustomerInfos->getCompany($result['ssi_prototype_request']['bill_company_cd'], $result['ssi_prototype_request']['bill_branch_cd']);
        }

        $result['ssi_prototype_request']['billing_user_name'] = null;
        $result['ssi_prototype_request']['accounting_user_name'] = null;

        if(!empty($result['ssi_prototype_request']['billing_user_cd'])) {
            $billing_user_name = $this->getUserName($result['ssi_prototype_request']['billing_user_cd']);
            $result['ssi_prototype_request']['billing_user_name'] = $billing_user_name->user_name;
        }
        if(!empty($result['ssi_prototype_request']['accounting_user_cd'])) {
            $accounting_user_name = $this->getUserName($result['ssi_prototype_request']['accounting_user_cd']);
            $result['ssi_prototype_request']['accounting_user_name'] = $accounting_user_name->user_name;
        }

        $result['ssi_prototype_request']['billing_organization_name'] = null;
        $result['ssi_prototype_request']['accounting_organization_name'] = null;
        if(!empty($result['ssi_prototype_request']['billing_organization'])) {
            $billing_organization_name = $this->getOrganizationName($result['ssi_prototype_request']['billing_organization']);
            $result['ssi_prototype_request']['billing_organization_name'] = $billing_organization_name->organization_name;
        }
        if(!empty($result['ssi_prototype_request']['accounting_organization'])) {
            $accounting_organization_name = $this->getOrganizationName($result['ssi_prototype_request']['accounting_organization']);
            $result['ssi_prototype_request']['accounting_organization_name'] = $accounting_organization_name->organization_name;
        }

        //customer_delivery
        $result['customer_delivery'] = null;

        if (!empty($result['ssi_prototype_request']['delivery_company_cd']) && !empty($result['ssi_prototype_request']['delivery_branch_cd'])) {
            $result['customer_delivery'] = $this->SsiCustomerInfos->getCompany($result['ssi_prototype_request']['delivery_company_cd'], $result['ssi_prototype_request']['delivery_branch_cd']);
        }

        $count_file_a01 =  AppUtility::getExitFilesCount('SR0301P0', 'A01', $result['ssi_prototype_request']->functionId);
        $count_file_a02 =  AppUtility::getExitFilesCount('SR0301P0', 'A02', $result['ssi_prototype_request']->functionId);
        $count_file_a03 =  AppUtility::getExitFilesCount('SR0301P0', 'A03', $result['ssi_prototype_request']->functionId);
        $count_file_a04 =  AppUtility::getExitFilesCount('SR0301P0', 'A04', $result['ssi_prototype_request']->functionId);
        $result['count_file_a01'] = $count_file_a01;
        $result['count_file_a02'] = $count_file_a02;
        $result['count_file_a03'] = $count_file_a03;
        $result['count_file_a04'] = $count_file_a04;

        $result['pulldownConfig'] = $this->loadConfigurePulldown();

        return $result;
    }

    private function getPrototypePrescriptionsNew($quotationInfo) {
        $result = array(
            array(),
            array(),
            array(),
            array(),
        );

        foreach ($result as $key => $value) {
            $header[$key] = $this->materialHeader($key);
        }

        $ssiEstimateMaterialsInfos = $this->SsiEstimateMaterialsInfos->getListMaterialForPrototype($quotationInfo['case_management_no'], $quotationInfo['est_branch_no'], $quotationInfo['prototype_request_branch_no'], $quotationInfo['prototype_result_branch_no']);

        foreach ($ssiEstimateMaterialsInfos as $key => $masterial) {
            $item = [
                'material_kbn' => $masterial['est_material_kbn'] - 1,
                'material_seq' => $masterial['est_gyo_no'],
                'item_cd' => $masterial['est_itemcd'],
                'est_item_general_name' => $masterial['est_item_general_name'],
                'estimate_normal_name' => $masterial['estimate_normal_name'],
                'est_item_name' => $masterial['est_item_name'],
                'est_use_unit_price' => $masterial['est_use_unit_price'],
                'company_cd' => $masterial['company_cd'],
                'branch_cd' => $masterial['branch_cd'],
                'maker_name' => $masterial['maker_name'],
                'supplier_company_cd' => $masterial['supplier_company_cd'],
                'supplier_branch_cd' => $masterial['supplier_branch_cd'],
                'supplier_name' => $masterial['supplier_name'],
                'est_item_tehai' => $masterial['est_item_tehai'],
                'est_item_tehai_name' => $masterial['est_item_tehai_name'],
                'order_unit' => $masterial['order_unit'],
                'est_item_tehai_spec' => $masterial['est_item_tehai_spec'],
                'est_item_tehai_spec_name' => $masterial['est_item_tehai_spec_name'],
                'blendinig_ratio' => $masterial['est_material_kbn'] == 4 ? $masterial['input_qty'] : ($quotationInfo['charge_unit'] == 'kg' ? $masterial['input_ratio'] : $masterial['input_qty']),
                'display_unit' =>   $masterial['est_material_kbn'] == 4 ? '' : ($quotationInfo['charge_unit'] == 'kg' ? '%' : 'mg'),
                'blending_unit' => $quotationInfo['charge_unit'],
                'act_qty' => $masterial['need_qty'],
                'provided_qty' => 0,
                'provided_spc' => '',
                'provided_date' => '',
                'note' => $masterial['est_item_note'],
                'total_row' => false,
            ];

            $header[$masterial['est_material_kbn'] - 1]['blendinig_ratio'] += $item['blendinig_ratio'];
            $header[$masterial['est_material_kbn'] - 1]['est_use_unit_price'] += $item['est_use_unit_price'];
            $header[$masterial['est_material_kbn'] - 1]['provided_qty'] += $item['provided_qty'];
            $header[$masterial['est_material_kbn'] - 1]['act_qty'] += $item['act_qty'];
            array_push($result[$masterial['est_material_kbn'] - 1], $item);
        }

        foreach ($result as $key => &$value) {
            array_unshift($result[$key], $header[$key]);
        }
        return $result;
    }

    private function getPrototypePrescriptions($prototypeRequest) {
        $result = array(
            array(),
            array(),
            array(),
            array(),
        );

        foreach ($result as $key => $value) {
            $header[$key] = $this->materialHeader($key);
        }

        $ssiProtypePrescriptions = $this->SsiProtypePrescriptions->getListProtypePrescriptions($prototypeRequest['case_management_no'], $prototypeRequest['est_branch_no'], $prototypeRequest['prototype_request_branch_no']);

        foreach ($ssiProtypePrescriptions as $key => $prescription) {
            $item = [
                'material_kbn' => $prescription['material_kbn'] - 1,
                'material_seq' => $prescription['material_seq'],
                'item_cd' => $prescription['item_cd'],
                'est_item_general_name' => $prescription['est_item_general_name'],
                'estimate_normal_name' => $prescription['estimate_normal_name'],
                'est_item_name' => $prescription['est_item_name'],
                'est_use_unit_price' => $prescription['est_use_unit_price'],
                'company_cd' => $prescription['company_cd'],
                'branch_cd' => $prescription['branch_cd'],
                'maker_name' => $prescription['maker_name'],
                'supplier_company_cd' => $prescription['supplier_company_cd'],
                'supplier_branch_cd' => $prescription['supplier_branch_cd'],
                'supplier_name' => $prescription['supplier_name'],
                'est_item_tehai' => $prescription['est_item_tehai'],
                'est_item_tehai_name' => $prescription['est_item_tehai_name'],
                'order_unit' => $prescription['order_unit'],
                'est_item_tehai_spec' => $prescription['est_item_tehai_spec'],
                'est_item_tehai_spec_name' => $prescription['est_item_tehai_spec_name'],
                'blendinig_ratio' => $prescription['blendinig_ratio'],
                'display_unit' =>   $prescription['material_kbn'] == 4 ? '' : ($prescription['blending_unit'] == 'kg' ? '%' : 'mg'),
                'blending_unit' => $prescription['blending_unit'],
                'act_qty' => $prescription['act_qty'],
                'provided_qty' => $prescription['provided_qty'],
                'provided_spc' => $prescription['provided_spc'],
                'provided_date' => $this->date->convertDateToString($prescription['provided_date']),
                'note' => $prescription['note'],
                'total_row' => false,
            ];

            $header[$prescription['material_kbn'] - 1]['blendinig_ratio'] += $item['blendinig_ratio'];
            $header[$prescription['material_kbn'] - 1]['est_use_unit_price'] += $item['est_use_unit_price'];
            $header[$prescription['material_kbn'] - 1]['provided_qty'] += $item['provided_qty'];
            $header[$prescription['material_kbn'] - 1]['act_qty'] += $item['act_qty'];
            array_push($result[$prescription['material_kbn'] - 1], $item);
        }

        foreach ($result as $key => &$value) {
            array_unshift($result[$key], $header[$key]);
        }

        return $result;
    }

    private function getPrototypePkgInfoNew($quotationInfo) {
        $header = $this->pkgHeader();

         $result = $this->SsiEstimatePkgInfos->getListPkgInfoForPrototype( $quotationInfo['case_management_no'], $quotationInfo['est_branch_no'], $quotationInfo['prototype_request_branch_no'], $quotationInfo['prototype_result_branch_no']);

        foreach ($result as $key => $value) {
            $header['use_unit_price'] += $value['use_unit_price'];
            $header['quantity'] += $value['quantity'];
            $header['tot_amt'] += $value['tot_amt'];
        }

        array_unshift($result, $header);

        return $result;
    }

    private function getPrototypePkgInfo($prototypeRequest) {
        $header = $this->pkgHeader();

         $result = $this->SsiPrototypeRequestPkgInfos->getListPrototypeRequestPkgInfos($prototypeRequest['case_management_no'], $prototypeRequest['est_branch_no'], $prototypeRequest['prototype_request_branch_no']);

        foreach ($result as $key => $value) {
            $header['use_unit_price'] += $value['use_unit_price'];
            $header['quantity'] += $value['quantity'];
            $header['tot_amt'] += $value['tot_amt'];
        }

        array_unshift($result, $header);

        return $result;
    }

    private function getPrototypePrescriptionResult($prototypeRequest) {
        $oneTab = array(
            array(),
            array(),
            array(),
            array(),
        );

        $result = array();

        for ($i = 1; $i <= 5; $i++) {
            $result[$i] = $oneTab;
        }

        //add row total
        foreach ($result as $tab => $prototypeResult) {
            foreach ($prototypeResult as $dtl => $value) {
                $header[$tab][$dtl] = $this->materialHeader($dtl);
            }
        }

        // get data db
        $prototypeResultDB = $this->SsiProtypePrescriptionResults->getProPrescriptionResult($prototypeRequest['case_management_no'], $prototypeRequest['est_branch_no'], $prototypeRequest['prototype_request_branch_no']);
        if(!empty($prototypeResultDB)) {

            foreach ($prototypeResultDB as $key => $value) {
                $result[$value['prototype_result_branch_no']][$value['material_kbn']][$value['material_seq']] = $value;
                $result[$value['prototype_result_branch_no']][$value['material_kbn']][$value['material_seq']]['display_unit'] = $value['material_kbn'] == 3 ? '' : ($value['blending_unit'] == 'kg' ? '%' : 'mg');

                $result[$value['prototype_result_branch_no']][$value['material_kbn']][$value['material_seq']]['provided_date'] = $this->date->convertDateToString($value['provided_date']);

                $header[$value['prototype_result_branch_no']][$value['material_kbn']]['blendinig_ratio'] += $value['blendinig_ratio'];
                $header[$value['prototype_result_branch_no']][$value['material_kbn']]['provided_qty'] += $value['provided_qty'];
                $header[$value['prototype_result_branch_no']][$value['material_kbn']]['est_use_unit_price'] += $value['est_use_unit_price'];
                $header[$value['prototype_result_branch_no']][$value['material_kbn']]['act_qty'] += $value['act_qty'];
            }
        }
        foreach ($result as $tab => &$prototypeResult) {
            foreach ($prototypeResult as $dtl => &$value) {
                array_unshift($value, $header[$tab][$dtl]);
            }
        }
        return $result;
    }

    private function getPrototypeResultPkgInfos($prototypeRequest) {
        $result = [];

        for ($i = 1; $i <= 5; $i++) {
            $result[$i] = array();
        }

        foreach ($result as $tab => $value) {
            $header[$tab] = $this->pkgHeader();
        }

        // get data db
        $resultPkgInfosDb = $this->SsiPrototypeResultPkgInfos->getListProResultPkgInfo($prototypeRequest['case_management_no'], $prototypeRequest['est_branch_no'], $prototypeRequest['prototype_request_branch_no']);

        foreach ($resultPkgInfosDb as $resultPkgInfo) {
            $result[$resultPkgInfo->prototype_result_branch_no][$resultPkgInfo->gyo_no] = $resultPkgInfo;
            $header[$resultPkgInfo->prototype_result_branch_no]['use_unit_price'] += $resultPkgInfo->use_unit_price;
            $header[$resultPkgInfo->prototype_result_branch_no]['quantity'] += $resultPkgInfo->quantity;
            $header[$resultPkgInfo->prototype_result_branch_no]['tot_amt'] += $resultPkgInfo->tot_amt;
        }

        foreach ($result as $tab => &$value) {
            array_unshift($value, $header[$tab]);
        }
        return $result;
    }

    public function getMaterialResultData($prototyeReusltInfo) {
        $data = $this->SsiProtypePrescriptionResults->getDataByBranchNo($prototyeReusltInfo['case_management_no'], $prototyeReusltInfo['est_branch_no'], $prototyeReusltInfo['prototype_request_branch_no'], $prototyeReusltInfo['prototype_result_branch_no']);

        $result = array(
            array(),
            array(),
            array(),
            array(),
        );

        foreach ($result as $dtl => $value) {
            $header[$dtl] = $this->materialHeader($dtl);
        }

        foreach ($data as $kbn => $item) {
            $result[$item['material_kbn']][$item['material_seq']] = $item;
            $result[$item['material_kbn']][$item['material_seq']]['display_unit'] = $item['material_kbn'] == 3 ? '' : ($item['blending_unit'] == 'kg' ? '%' : 'mg');
            $header[$item['material_kbn']]['blendinig_ratio'] += $item['blendinig_ratio'];
            $header[$item['material_kbn']]['est_use_unit_price'] += $item['est_use_unit_price'];
            $header[$item['material_kbn']]['act_qty'] += $item['act_qty'];
            $header[$item['material_kbn']]['provided_qty'] += $item['provided_qty'];
        }

        foreach ($result as $tab => &$value) {
            array_unshift($value, $header[$tab]);
        }

        return $result;
    }

    public function getPkgResultData($prototyeReusltInfo) {
        $header = $this->pkgHeader();

        $result =$this->SsiPrototypeResultPkgInfos->getListProResultPkgInfo($prototyeReusltInfo['case_management_no'], $prototyeReusltInfo['est_branch_no'], $prototyeReusltInfo['prototype_request_branch_no'], $prototyeReusltInfo['prototype_result_branch_no']);

       foreach ($result as $key => $value) {
           $header['use_unit_price'] += $value['use_unit_price'];
           $header['quantity'] += $value['quantity'];
           $header['tot_amt'] += $value['tot_amt'];
       }

       array_unshift($result, $header);

       return $result;
    }

    public function add($id = null)
    {
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $data = $this->formatPrototypeRequest($data);
            $ssiPrototypeRequests = $this->storeData($data);
            return $this->redirect(array('action'=>'view', $ssiPrototypeRequests->id));
        }

        $data = $this->getData($id, null);
        $this->set(compact('data'));
        $this->set('_serialize', ['data']);
    }

    public function copy($id){
        if ($this->request->is('put')) {
            $data = $this->request->getData();
            $data = $this->formatPrototypeRequest($data);
            unset($data['ssi_prototype_request']['id']);
            $ssiPrototypeRequests = $this->storeData($data);
            return $this->redirect(array('action'=>'view', $ssiPrototypeRequests['id']));
        }
        $data = $this->getData(null, $id, true);
        unset($data['ssi_prototype_request']->id);
        // unset ssi_prototype_request_billing_id
        if(!empty($data['ssi_prototype_request_billings'])){
            foreach ($data['ssi_prototype_request_billings'] as $value){
                unset($value['id']);
            }
        }
        if(!empty($data['ssi_prototype_request_shippings'])){
            foreach ($data['ssi_prototype_request_shippings'] as $value){
                unset($value['id']);
            }
        }
        $this->set(compact('data'));
        $this->render('add');
    }


    private function storeData($data) {
        $connection = ConnectionManager::get('default');
        $connection->begin();
        try {
            $user = $this->Auth->user();
            // save ssi prototype request
            $prototypeRequestEntity = $this->SsiPrototypeRequests->newEntity();

            if(empty($data['ssi_prototype_request']['id'])) {
                $prototypeMaxBranch = $this->SsiPrototypeRequests->find('all')
                    ->where(['case_management_no' => $data['ssi_prototype_request']['case_management_no']])
                    ->where(['est_branch_no' =>$data['ssi_prototype_request']['est_branch_no']])
                    ->max('prototype_request_branch_no');
                $data['ssi_prototype_request']['prototype_request_branch_no'] = empty($prototypeMaxBranch) ? 1 : $prototypeMaxBranch->prototype_request_branch_no + 1;
                $data['ssi_prototype_request']['created_usrcd'] = $user['user_cd'];
            }

            $data['ssi_prototype_request']['modified_usrcd'] =  $user['user_cd'];

            $prototypeRequestEntity = $this->SsiPrototypeRequests->patchEntity($prototypeRequestEntity, $data['ssi_prototype_request']);
            $prototypeRequestEntity = $this->setPrototypeEntity($prototypeRequestEntity, $data['ssi_prototype_request']);

            $ssiPrototypeRequests = $this->SsiPrototypeRequests->save($prototypeRequestEntity);
            if ($ssiPrototypeRequests) {
                // save ssi_prototype_request_billings
                $prototypeId = $ssiPrototypeRequests->id;
                if (!empty($data['ssi_prototype_request_billings'])){
                    $this->SsiPrototypeRequestBillings->savePrototypeRequestBilling($data['ssi_prototype_request_billings'], $prototypeId, $user);
                }

                  // save ssi prototype
                if (!empty($data['ssi_prototype_request_shippings'])){
                    $this->SsiPrototypeRequestShippings->saveSsiPrototypeRequestShippings($data['ssi_prototype_request_shippings'], $prototypeId, $user);
                }

                // deleted save ssi prototype prescription
                $this->SsiProtypePrescriptions->deleteAll([
                    'case_management_no' => $data['ssi_prototype_request']['case_management_no'],
                    'est_branch_no' => $data['ssi_prototype_request']['est_branch_no'],
                    'prototype_request_branch_no' => $data['ssi_prototype_request']['prototype_request_branch_no'],
                ]);

                $prototype_prescriptions = json_decode($data['prototype_prescriptions']);
                foreach ($prototype_prescriptions as $dtl => $prototype_prescription) {
                    foreach ($prototype_prescription as $row => $prototype) {
                        if (empty($prototype->total_row)) {
                            // new entity
                            $prototypePersciptionEntity = $this->SsiProtypePrescriptions->newEntity();
                            $this->SsiProtypePrescriptions->patchEntity($prototypePersciptionEntity, (Array) $prototype);

                            // UPDATE data
                            $prototypePersciptionEntity->case_management_no = $data['ssi_prototype_request']['case_management_no'];
                            $prototypePersciptionEntity->est_branch_no = $data['ssi_prototype_request']['est_branch_no'];
                            $prototypePersciptionEntity->prototype_request_branch_no = $data['ssi_prototype_request']['prototype_request_branch_no'];
                            $prototypePersciptionEntity->material_kbn = $dtl + 1;
                            $prototypePersciptionEntity->material_seq = $row;

                            $provided_date = date_create_from_format('Y/m/d', $prototype->provided_date);

                            $prototypePersciptionEntity->provided_date = $provided_date ? $provided_date : null;
                            //save data
                            $this->SsiProtypePrescriptions->save($prototypePersciptionEntity);
                        }
                    }
                }

                // deleted save SsiPrototypeRequestPkgInfos
                $this->SsiPrototypeRequestPkgInfos->deleteAll([
                    'case_management_no' => $data['ssi_prototype_request']['case_management_no'],
                    'est_branch_no' => $data['ssi_prototype_request']['est_branch_no'],
                    'prototype_request_branch_no' => $data['ssi_prototype_request']['prototype_request_branch_no'],
                ]);

                //save ssi_prototype_request_pkg_info
                $ssiPrototypeRequestPkgInfos = json_decode($data['prototype_pkg_info']);
                foreach ($ssiPrototypeRequestPkgInfos as $row => $ssiPrototypeRequestPkgInfo) {
                    if (empty($ssiPrototypeRequestPkgInfo->total_row)) {
                        // new entity
                        $ssiPrototypeRequestPkgInfoEntity = $this->SsiPrototypeRequestPkgInfos->newEntity();
                        $this->SsiPrototypeRequestPkgInfos->patchEntity($ssiPrototypeRequestPkgInfoEntity, (Array) $ssiPrototypeRequestPkgInfo);

                        // UPDATE data
                        $ssiPrototypeRequestPkgInfoEntity->case_management_no = $data['ssi_prototype_request']['case_management_no'];
                        $ssiPrototypeRequestPkgInfoEntity->est_branch_no = $data['ssi_prototype_request']['est_branch_no'];
                        $ssiPrototypeRequestPkgInfoEntity->prototype_request_branch_no = $data['ssi_prototype_request']['prototype_request_branch_no'];
                        $ssiPrototypeRequestPkgInfoEntity->gyo_no = $row;

                        //save data
                        $this->SsiPrototypeRequestPkgInfos->save($ssiPrototypeRequestPkgInfoEntity);
                    }
                }

                // save ssi_protype_result_info
                $prototypeResultIds = json_decode($data['prototype_result_ids']);
                $ssiPrototypePrescriptionResults = json_decode($data['ssi_prototype_prescription_results']);
                $ssiPrototypeResultPkgInfos = json_decode($data['ssi_prototype_result_pkg_infos']);

                // deleted save SsiProtypePrescriptionResults
                $this->SsiProtypePrescriptionResults->deleteAll([
                    'case_management_no' => $data['ssi_prototype_request']['case_management_no'],
                    'est_branch_no' => $data['ssi_prototype_request']['est_branch_no'],
                    'prototype_request_branch_no' => $data['ssi_prototype_request']['prototype_request_branch_no'],
                ]);

                // deleted save ssiPrototypeResultPkgInfos
                $this->SsiPrototypeResultPkgInfos->deleteAll([
                    'case_management_no' => $data['ssi_prototype_request']['case_management_no'],
                    'est_branch_no' => $data['ssi_prototype_request']['est_branch_no'],
                    'prototype_request_branch_no' => $data['ssi_prototype_request']['prototype_request_branch_no'],
                ]);

                foreach ($prototypeResultIds as $id) {
                    // new entity
                    $ssiPrototypeResultInfoEntity = $this->SsiProtypeResultInfos->newEntity();
                    $this->SsiProtypeResultInfos->patchEntity($ssiPrototypeResultInfoEntity, (Array) $data['ssi_prototype_result_info'][$id]);

                    // UPDATE data
                    $ssiPrototypeResultInfoEntity->case_management_no = $data['ssi_prototype_request']['case_management_no'];
                    $ssiPrototypeResultInfoEntity->est_branch_no = $data['ssi_prototype_request']['est_branch_no'];
                    $ssiPrototypeResultInfoEntity->prototype_request_branch_no = $data['ssi_prototype_request']['prototype_request_branch_no'];
                    $ssiPrototypeResultInfoEntity->prototype_result_branch_no = $id;
                    $ssiPrototypeResultInfoEntity->user_cd = $data['ssi_prototype_result_info'][$id]['user_cd'];
                    $ssiPrototypeResultInfoEntity->organization_cd = $data['ssi_prototype_result_info'][$id]['organization_cd'];
                    $ssiPrototypeResultInfoEntity->sale_status = $data['ssi_prototype_result_info'][$id]['sale_status'];
                    $ssiPrototypeResultInfoEntity->prototype_complete = !empty($data['ssi_prototype_result_info'][$id]['prototype_complete']) ? $data['ssi_prototype_result_info'][$id]['prototype_complete']: NULL;

                    $ssiPrototypeResultInfoEntity->pkg_organization_cd = $data['ssi_prototype_result_info'][$id]['pkg_organization_cd'];
                    $ssiPrototypeResultInfoEntity->pkg_user_cd = $data['ssi_prototype_result_info'][$id]['pkg_user_cd'];


                    $ssiPrototypeResultInfoEntity->formulation_completed_date = !empty($data['ssi_prototype_result_info'][$id]['formulation_completed_date']) ? date_create_from_format('Y/m/d', $data['ssi_prototype_result_info'][$id]['formulation_completed_date']) : NULL;

                    $ssiPrototypeResultInfoEntity->pkg_completed_date = !empty($data['ssi_prototype_result_info'][$id]['pkg_completed_date']) ? date_create_from_format('Y/m/d', $data['ssi_prototype_result_info'][$id]['pkg_completed_date']) : NULL;
                    $ssiPrototypeResultInfoEntity->pkg_complete_plan_date = !empty($data['ssi_prototype_result_info'][$id]['pkg_complete_plan_date']) ? date_create_from_format('Y/m/d', $data['ssi_prototype_result_info'][$id]['pkg_complete_plan_date']): NULL;

                    $veryfied_delivdate = date_create_from_format('Y/m/d', $data['ssi_prototype_request']['prototype_complete_date']);

                    $ssiPrototypeResultInfoEntity->veryfied_delivdate = $veryfied_delivdate ? $veryfied_delivdate->format('Y-m-d') : null;

                    $protoype_complete_planted = date_create_from_format('Y/m/d', $data['ssi_prototype_result_info'][$id]['protoype_complete_planted']);

                    $ssiPrototypeResultInfoEntity->protoype_complete_planted = $protoype_complete_planted ? $protoype_complete_planted->format('Y-m-d') : null;

                    $ssiPrototypeResultInfoEntity['created_usrcd'] = $user['user_cd'];
                    $ssiPrototypeResultInfoEntity['modified_usrcd'] = $user['user_cd'];
                    //save data
                    $this->SsiProtypeResultInfos->save($ssiPrototypeResultInfoEntity);

                    // save ssi_protype_prescription_result
                    $ssiPrototypePrescriptionResult = $ssiPrototypePrescriptionResults->$id;

                    foreach ($ssiPrototypePrescriptionResult as $table => $ssiPrototypePrescription) {
                        foreach ($ssiPrototypePrescription as $row => $value) {
                            if (empty($value->total_row)) {
                                // new entity
                                $ssiPrototypePrescriptionResultEntity = $this->SsiProtypePrescriptionResults->newEntity();
                                $this->SsiProtypePrescriptionResults->patchEntity($ssiPrototypePrescriptionResultEntity, (Array) $value);

                                // UPDATE data
                                $ssiPrototypePrescriptionResultEntity->case_management_no = $data['ssi_prototype_request']['case_management_no'];
                                $ssiPrototypePrescriptionResultEntity->est_branch_no = $data['ssi_prototype_request']['est_branch_no'];
                                $ssiPrototypePrescriptionResultEntity->prototype_request_branch_no = $data['ssi_prototype_request']['prototype_request_branch_no'];
                                $ssiPrototypePrescriptionResultEntity->prototype_result_branch_no = $id;
                                $ssiPrototypePrescriptionResultEntity->material_seq = $row;

                                //save data
                                $this->SsiProtypePrescriptionResults->save($ssiPrototypePrescriptionResultEntity);
                            }
                        }
                    }

                    //save ssi_prototype_result_pkg_info
                    $ssiPrototypeResultPkgInfo = $ssiPrototypeResultPkgInfos->$id;
                    foreach ($ssiPrototypeResultPkgInfo as $row => $value) {
                        if (empty($value->total_row)) {
                            // new entity
                            $ssiPrototypeResultPkgInfoEntity = $this->SsiPrototypeResultPkgInfos->newEntity();
                            $this->SsiPrototypeResultPkgInfos->patchEntity($ssiPrototypeResultPkgInfoEntity, (Array) $value);

                            // UPDATE data
                            $ssiPrototypeResultPkgInfoEntity->case_management_no = $data['ssi_prototype_request']['case_management_no'];
                            $ssiPrototypeResultPkgInfoEntity->est_branch_no = $data['ssi_prototype_request']['est_branch_no'];
                            $ssiPrototypeResultPkgInfoEntity->prototype_request_branch_no = $data['ssi_prototype_request']['prototype_request_branch_no'];
                            $ssiPrototypeResultPkgInfoEntity->prototype_result_branch_no = $id;
                            $ssiPrototypeResultPkgInfoEntity->gyo_no = $row;

                            //save data
                            $this->SsiPrototypeResultPkgInfos->save($ssiPrototypeResultPkgInfoEntity);
                        }
                    }
                }
                $connection->commit();
                if(empty($data['ssi_prototype_request']['id'])) {
                    $upload_id = $data['companyId'];
                    if(isset($upload_id)) {
                        CommonController::updateFileTable(Configure::read('FOLDER_PRE_NAME')['prototype'].$ssiPrototypeRequests['case_management_no']. $ssiPrototypeRequests['est_branch_no'].  $ssiPrototypeRequests['prototype_request_branch_no'], $upload_id);
                    }
                }
                return $ssiPrototypeRequests;
            } else {
                $connection->rollback();
            }
        } catch (Exception $ex) {
            $this->Flash->error(__($ex->getMessage()));
            $this->connection->rollback();
        }
    }

    public function edit($id = null)
    {
        if ($this->request->is('put')) {
            $data = $this->request->getData();
            $data = $this->formatPrototypeRequest($data);
            $ssiPrototypeRequests = $this->storeData($data);

            return $this->redirect(array('action'=>'view', $id));
        }

        $data = $this->getData(null, $id);
        $this->set(compact('data', 'prototypeRequest', 'quotationInfo', 'saleStaff', 'customer', 'firstMaterialDetail', 'secondMaterialDetail','prototypeRequestNews',
            'thirdMaterialDetail', 'pkgInfoData', 'functionId', 'caseKbn', 'request', 'analysisRequestList'));
        $this->set('_serialize', ['prototypeRequest', 'quotationInfo', 'saleStaff', 'customer', 'firstMaterialDetail', 'secondMaterialDetail',
            'thirdMaterialDetail', 'pkgInfoData', 'functionId', 'caseKbn', 'request']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Prototype Request id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        // get prototype_request
        $prototypeRequest = $this->SsiPrototypeRequests->get($this->request->getData('id'));
        $case_no = $prototypeRequest->case_management_no;
        $est_no = $prototypeRequest->est_branch_no;
        $request_no = $prototypeRequest->prototype_request_branch_no;
        $user = $this->Auth->user();

        $flag = true;

        $connection = ConnectionManager::get('default');
        $connection->begin();
        try {
            $flag = $this->SsiPrototypeRequests->softRemoveProRequests($prototypeRequest->id, $user); //done

            if($flag == true) {
                $flag = AppUtility::softRemoveTableData('SsiPrototypeRequestBillings', $prototypeRequest->id, $user['user_cd'], "prototype_id");
            }

            if($flag == true) {
                $flag = $this->softRemoveByBranchNo('SsiProtypePrescriptions', $case_no, $est_no, $request_no, $user);
            }
            if($flag == true) {
                $flag = AppUtility::softRemoveTableData('SsiPrototypeRequestShippings', $prototypeRequest->id , $user['user_cd'], 'prototype_id');
            }

            if($flag == true) {
                $flag = $this->softRemoveByBranchNo('SsiPrototypeRequestPkgInfos', $case_no, $est_no, $request_no, $user);
            }

            if($flag == true) {
                $flag = $this->SsiProtypeComment->softRemoveProComment($case_no, $est_no, $request_no, $user);
            }

            if($flag == true) {
                $flag = $this->softRemoveByBranchNo('SsiProtypeResultInfos', $case_no, $est_no, $request_no, $user);
            }

            if($flag == true) {
                $flag = $this->softRemoveByBranchNo('SsiProtypePrescriptionResults', $case_no, $est_no, $request_no, $user);
            }

            if($flag == true) {
                $flag = $this->softRemoveByBranchNo('SsiPrototypeResultPkgInfos', $case_no, $est_no, $request_no, $user);
            }

            if($flag == true) {
                $flag = $this->softRemoveById('SsiPrototypeConfirmHistories', $prototypeRequest->id, $user);
            }

            if($flag == true) {
                $flag = $this->softRemoveById('SsiPrototypeConfirmRequestHistories', $prototypeRequest->id, $user);
            }

            if($flag == true) {
                $connection->commit();
                $this->Flash->success(__('規試作依頼が削除されました'));
            }else {
                $connection->rollback();
                $this->Flash->error(__('規試作依頼が削除できません。再度やり直してください'));
            }
        }catch(\Exception $ex) {
            $connection->rollback();
            $this->Flash->error(__('規試作依頼が削除できません。再度やり直してください'));
        }
        return $this->redirect(['action' => 'index']);
    }

    public function formatPrototypeRequest($data){
        if(!empty($data)){
            $data['ssi_prototype_request']['protoype_fee'] = AppUtility::formatToInt($data['ssi_prototype_request']['protoype_fee']);
            $data['ssi_prototype_request']['analysis_fee'] = AppUtility::formatToInt($data['ssi_prototype_request']['analysis_fee']);
            $data['ssi_prototype_request']['security_analysis_fee'] = AppUtility::formatToInt($data['ssi_prototype_request']['security_analysis_fee']);
            $data['ssi_prototype_request']['act_plan_qty'] = AppUtility::formatToInt($data['ssi_prototype_request']['act_plan_qty']);
            $data['ssi_prototype_request']['act_plan_amount'] = AppUtility::formatToInt($data['ssi_prototype_request']['act_plan_amount']);
            $data['ssi_prototype_request']['expensive'] = AppUtility::formatToInt($data['ssi_prototype_request']['expensive']);
        }
        return $data;
    }
    public function loadConfigurePulldown(){
        $result = array();

        $result['sale_status'] = Configure::read('SALE_STATUS');

        return $result;
    }

    public function getUserName($user_cd) {
        $result = "";
        $result = $this->SsiUsers->find()
            ->select([
                'user_name'
            ])
            ->where([
                "user_cd" => $user_cd
            ])
            ->first();
        return $result;
    }

    public function getOrganizationName($organization_cd) {
        $result = "";
        $result = $this->SsiOrganizations->find()
            ->select([
                'organization_name'
            ])
            ->where([
                "organization_cd" => $organization_cd
            ])
            ->first();
        return $result;
    }

    public function deleteResultInfo() {
        if ($this->request->is('ajax')) {

            $result_id = $this->request->data['result_id'];
            $valid = false;
            $prototypeResultInfo = $this->SsiProtypeResultInfos->get($result_id);
            if(!empty($prototypeResultInfo)) {
                if($this->SsiProtypeResultInfos->deleteAll([
                    'id' => $result_id
                ])) {
                // deleted save SsiProtypePrescriptionResults
                $this->SsiProtypePrescriptionResults->deleteAll([
                    'case_management_no' => $prototypeResultInfo->case_management_no,
                    'est_branch_no' => $prototypeResultInfo->est_branch_no,
                    'prototype_request_branch_no' => $prototypeResultInfo->prototype_request_branch_no,
                    'prototype_result_branch_no' => $prototypeResultInfo->prototype_result_branch_no,
                ]);

                // deleted save ssiPrototypeResultPkgInfos
                $this->SsiPrototypeResultPkgInfos->deleteAll([
                    'case_management_no' => $prototypeResultInfo->case_management_no,
                    'est_branch_no' => $prototypeResultInfo->est_branch_no,
                    'prototype_request_branch_no' => $prototypeResultInfo->prototype_request_branch_no,
                    'prototype_result_branch_no' => $prototypeResultInfo->prototype_result_branch_no,
                ]);
                    $valid = true;
                }
            }


			$this->response->type('json');
            $this->response->body(json_encode($valid));
            return $this->response;
		}
    }

    public function materialHeader($kbn) {
        return [
            'case_management_no' => null,
            'est_branch_no' => null,
            'prototype_request_branch_no' => null,
            'material_kbn' => $kbn,
            'material_seq' => 0,
            'item_cd' => '',
            'est_item_general_name' => '',
            'estimate_normal_name' => '',
            'est_item_name' => '',
            'est_use_unit_price' => 0,
            'company_cd' => '',
            'branch_cd' => '',
            'maker_name' => '',
            'supplier_company_cd' => '',
            'supplier_branch_cd' => '',
            'supplier_name' => '',
            'est_item_tehai' => '',
            'est_item_tehai_name' => '',
            'order_unit' => null,
            'est_item_tehai_spec' => '',
            'est_item_tehai_spec_name' => '',
            'blendinig_ratio' => 0,
            'blending_unit' => '',
            'act_qty' => 0,
            'provided_qty' => 0,
            'provided_spc' => '',
            'provided_date' => '',
            'total_row' => true,
            'note' =>null,
            'display_unit' => '',
        ];
    }
    public function pkgHeader() {
        return [
            'item_cd' => '',
            'item_name' => '',
            'item_kigou' => '',
            'use_unit_price' => 0,
            'maker_company_cd' => '',
            'maker_branch_cd' => '',
            'maker_name' => '',
            'order_destination_company_cd' => '',
            'order_destination_branch_cd' => '',
            'order_destination_name' => '',
            'item_tehai' => '',
            'item_tehai_name' => '',
            'item_tehai_spec' => '',
            'item_tehai_spec_name' => '',
            'quantity' => 0,
            'tot_amt' => 0,
            'est_item_note' => '',
            'total_row' => true,
        ];
    }

    public function setLastUserConfirm() {
        if($this->request->is('post')) {
            $valid = array('flag' => false, 'user_name' => '', 'created_date' => '','message' => '');

            $user = $this->Auth->user();
            $prototype_id = $this->request->getData('prototype_id');

            $connection = ConnectionManager::get('default');
            $connection->begin();

            try {
                $prototypConfirmEntity = $this->SsiPrototypeConfirmHistories->newEntity();

                $prototypConfirmEntity->prototype_id = $prototype_id;
                $prototypConfirmEntity->user_cd = $user['user_cd'];
                $prototypConfirmEntity->created_usrcd = $user['user_cd'];
                $prototypConfirmEntity->modified_usrcd = $user['user_cd'];

                if($this->SsiPrototypeConfirmHistories->save($prototypConfirmEntity)) {
                    $connection->commit();
                    $valid['created_date'] = $this->date->formatDateTime($prototypConfirmEntity->created, 'Y/m/d H:i:s');
                    $valid['flag'] = true;
                    $valid['user_name'] = $user['user_name'];
                    $valid['message'] = '試作を承認しました。';
                }else {
                    $connection->rollback();
                }
            } catch (\Exception $ex) {
                $connection->rollback();
            }


            $this->response->type('json');
            $this->response->body(json_encode($valid));
            return $this->response;
        }
    }

    public function setUserRequestConfirm() {
        if($this->request->is('post')) {
            $valid = array('flag' => false, 'user_name' => '', 'created_date' => '','message' => '');

            $user = $this->Auth->user();
            $prototype_id = $this->request->getData('prototype_id');

            $connection = ConnectionManager::get('default');
            $connection->begin();

            try {
                $prototypConfirmEntity = $this->SsiPrototypeConfirmRequestHistories->newEntity();

                $prototypConfirmEntity->prototype_id = $prototype_id;
                $prototypConfirmEntity->user_cd = $user['user_cd'];
                $prototypConfirmEntity->created_usrcd = $user['user_cd'];
                $prototypConfirmEntity->modified_usrcd = $user['user_cd'];

                if($this->SsiPrototypeConfirmRequestHistories->save($prototypConfirmEntity)) {
                    $connection->commit();
                    $valid['created_date'] = $this->date->formatDateTime($prototypConfirmEntity->created, 'Y/m/d H:i:s');
                    $valid['flag'] = true;
                    $valid['user_name'] = $user['user_name'];
                    $valid['message'] = '試作の承認依頼を送りました。';
                }else {
                    $connection->rollback();
                }
            } catch (\Exception $ex) {
                $connection->rollback();
            }


            $this->response->type('json');
            $this->response->body(json_encode($valid));
            return $this->response;
        }
    }

    public function setProcessed() {
        if($this->request->is('post')) {
            $valid = array('flag' => false, 'message' => '');

            $prototype_id = $this->request->getData('prototype_id');
            $checked = $this->request->getData('checked');

            $connection = ConnectionManager::get('default');
            $connection->begin();

            try {
                $prototype = $this->SsiPrototypeRequests->get($prototype_id);

                if($checked == 1) {
                    $prototype->processed_flg = 1;
                }else {
                    $prototype->processed_flg = 0;
                }

                if($this->SsiPrototypeRequests->save($prototype)) {
                    $connection->commit();
                    $valid['flag'] = true;
                }else {
                    $connection->rollback();
                }

            } catch (\Exception $ex) {
                $connection->rollback();
            }

            $this->response->type('json');
            $this->response->body(json_encode($valid));
            return $this->response;
        }
    }

    public function softRemoveById($model_name,$id, $user){
        $flag = true;

        $model = TableRegistry::get($model_name);
        $data = $model->find()
            ->where([
                "prototype_id" => $id,
                "deleted IS NULL",
            ])
            ->toArray();

        if(!empty($data)) {
            foreach($data as $item) {
                if($flag == true) {
                    $entity = $model->patchEntity($item, [
                        'deleted_usrcd' => $user['user_cd'],
                        'deleted' => Time::now(),
                    ]);
                    if (!$model->save($entity)) {
                        $flag = false;
                    }
                }
            }
        }
        return $flag;
    }

    public function softRemoveByBranchNo($model_name, $case_no, $est_no, $request_no, $user) {
        $flag = true;

        $model = TableRegistry::get($model_name);

        $data = $model->find()
            ->where([
                "case_management_no" => $case_no,
                "est_branch_no" => $est_no,
                "prototype_request_branch_no" => $request_no,
                "deleted IS NULL",
            ])
            ->toArray();

        if(!empty($data)) {
            foreach($data as $item) {
                if($flag == true) {
                    $entity = $model->patchEntity($item, [
                        'deleted_usrcd' => $user['user_cd'],
                        'deleted' => Time::now(),
                    ]);

                    if (!$model->save($entity)) {
                        $flag = false;
                    }
                }
            }
        }
        return $flag;
    }

    private function setPrototypeEntity($entity, $data) {
        $entity->billing_wishful_date1 = isset($data['billing_wishful_date1']) ? $this->date->formatDateTimeFromString($data['billing_wishful_date1']) : null;
        $entity->billing_wishful_date2 = isset($data['billing_wishful_date2']) ? $this->date->formatDateTimeFromString($data['billing_wishful_date2']) : null;
        $entity->billing_wishful_date3 = isset($data['billing_wishful_date3']) ? $this->date->formatDateTimeFromString($data['billing_wishful_date3']) : null;
        $entity->billing_wishful_date4 = isset($data['billing_wishful_date4']) ? $this->date->formatDateTimeFromString($data['billing_wishful_date4']) : null;
        $entity->billing_wishful_date5 = isset($data['billing_wishful_date5']) ? $this->date->formatDateTimeFromString($data['billing_wishful_date5']) : null;
        $entity->prototype_complete_date = isset($data['prototype_complete_date']) ? $this->date->formatDateTimeFromString($data['prototype_complete_date']) : null;
        $entity->billing_date = isset($data['billing_date']) ? $this->date->formatDateTimeFromString($data['billing_date']) : null;
        $entity->act_plan_date = isset($data['act_plan_date']) ? $this->date->formatDateTimeFromString($data['act_plan_date']) : null;
        $entity->self_by_date = isset($data['self_by_date']) ? $this->date->formatDateTimeFromString($data['self_by_date']) : null;
        $entity->accounting_date = isset($data['accounting_date']) ? $this->date->formatDateTimeFromString($data['accounting_date']) : null;

        return $entity;
    }

    private function setPrototypePrescriptionsTableTitle($sheet, $row, $key) {
        $prototypePrescriptionsTableTitle = [
            '原料情報（内容液）',
            '原料情報（皮膜）',
            '原料情報（ｺｰﾃｨﾝｸﾞ）',
            '原料情報（ｶﾌﾟｾﾙ）'
        ];

        foreach ($prototypePrescriptionsTableTitle as $index => $value) {
            if($index == $key) {
                $sheet->setCellValue('B' . $row, $value);
                $sheet->getStyle('B' . $row)->applyFromArray(array('font' => array('bold' => true)));
            }
        }

        return $sheet;
    }

    private function setPrototypePrescriptionsTitle($sheet, $row, $kbn) {
        $cellList = $this->getPrototypePrescriptionsCellList($kbn);
        $titleList = $this->getPrototypePrescriptionsTitle($kbn);

        foreach($cellList as $key => $value) {
            $sheet->setCellValue($value[0] . $row, $titleList[$key]);
        }
    }

    private function getPrototypePrescriptionsTitle($kbn) {
        $titleList = $this->setPrototypePrescriptionsInfoTitle();

        return $titleList;
    }

    private function setPrototypePrescriptionsInfoTitle () {
        return [
            '',
            '',
            '品目ｺｰﾄﾞ',
            '一般名',
            '見積使用名',
            '商品名',
            '見積使用単価',
            '製造元',
            '発注先',
            '手配',
            '発注単位',
            '特殊',
            '配合量',
            '単位',
            '仕込量',
            '支給量',
            '在庫',
            '支給日',
            '備考',
        ];
    }

    private function getPrototypePrescriptionsCellList($kbn) {
        $cellList = [
            ['C', 'C'],
            ['D', 'D'],
            ['E', 'E'],
            ['F', 'F'],
            ['G', 'G'],
            ['H', 'H'],
            ['I', 'I'],
            ['J', 'J'],
            ['K', 'K'],
            ['L', 'L'],
            ['M', 'M'],
            ['N', 'N'],
            ['O', 'O'],
            ['P', 'P'],
            ['Q', 'Q'],
            ['R', 'R'],
            ['S', 'S'],
            ['T', 'T'],
            ['U', 'U']
        ];
        return $cellList;
    }

    private function mergePrototypePrescriptionsCell($sheet, $row, $kbn) {

        $cellList = $this->getPrototypePrescriptionsCellList($kbn);

        return $this->mergeCellInList($sheet, $cellList, $row);
    }

    private function mergeCellInList($sheet, $cellList, $row) {
        foreach($cellList as $value) {
            $sheet->mergeCells($value[0] . $row . ":" . $value[1] . $row);
        }

        return $sheet;
    }

    private function mergePkgInfoCell($sheet, $row) {
        $cellList = $this->getPkgCellList();

        return $this->mergeCellInList($sheet, $cellList, $row);
    }

    private function setPkgInfoTitle($sheet, $row) {
        $cellList = $this->getPkgCellList();

        $titleList = $this->getPkgInfoTitle();

        foreach($cellList as $key => $value) {
            $sheet->setCellValue($value[0] . $row, $titleList[$key]);
        }
    }

    private function getPkgCellList() {
        return [
            ['C', 'C'],
            ['D', 'D'],
            ['E', 'F'],
            ['G', 'H'],
            ['I', 'J'],
            ['K', 'L'],
            ['M', 'N'],
            ['O', 'P'],
            ['Q', 'Q'],
            ['R', 'R'],
            ['S', 'S'],
            ['T', 'T'],
            ['U', 'U'],
        ];
    }

    private function getPkgInfoTitle() {
        return [
            '',
            '',
            '品目ｺｰﾄﾞ',
            '商品名',
            '会記号',
            '見積使用単価',
            '製造元',
            '発注先',
            '手配',
            '特殊',
            '数量',
            '小計',
            '備考'
        ];
    }

    private function getHeaderStyle() {
        return array(
            'alignment' => array(
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('rgb' => '333333')
                )
            ),
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => 'cccccc')
            )
        );
    }

    private function getCellStyle() {
        return array(
            'borders' => array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('rgb' => '333333')
                )
            ),
        );
    }

    public function exportExcel($id) {
        $data = $this->getData(null, $id);
        $ssiPrototypeRequests = $data['ssi_prototype_request'];
        $ssiPrototypePrescriptions = $data['prototype_prescriptions'];
        $prototypePkgInfo = $data['prototype_pkg_info'];
        $excel = PHPExcel_IOFactory::createReader('Excel2007');
        $book = $excel->load(APP.'Template/PrototypeRequests/example_template.xlsx');
        $sheet = $book->getActiveSheet();
        $endColumn = 'U';
        $rowIndex = 3;
        $rowIndex = $this->readSsiPrototypePrescriptionExcel($ssiPrototypePrescriptions, $rowIndex, $sheet, $endColumn);
        $sheet->setCellValue('A'. $rowIndex, '■包材情報');
        $sheet->getStyle('A' . $rowIndex)->applyFromArray(array('font' => array('bold' => true)));
        $rowIndex += 2;
        $tmp =  $rowIndex + 1;

        $this->mergePkgInfoCell($sheet, $rowIndex);
        $sheet->getStyle('C'. $rowIndex . ':' . $endColumn . $rowIndex)->applyFromArray(
            $this->getHeaderStyle()
        );
        $this->setPkgInfoTitle($sheet, $rowIndex);
        $sheet->setCellValue('D'. $tmp, '合計');

        $this->setPrototypePkgInfoExcel($prototypePkgInfo, $rowIndex, $sheet, $endColumn);

        $sheet->setTitle('出力');
        $bookFileName = $ssiPrototypeRequests['prototype_no'].'_'.date('YmdHis');
        $writer = PHPExcel_IOFactory::createWriter($book, 'Excel2007');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment;filename="'.$bookFileName.'.xlsx"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');

        exit;
    }

    public function exportResultExcel($id) {
        $data = $this->SsiProtypeResultInfos->get($id);
        $prescriptionResults = $this->getMaterialResultData($data);
        $prototypePkgInfo = $this->getPkgResultData($data);
        $excel = PHPExcel_IOFactory::createReader('Excel2007');
        $book = $excel->load(APP.'Template/PrototypeRequests/example_template.xlsx');
        $sheet = $book->getActiveSheet();
        $endColumn = 'U';
        $rowIndex = 3;

        $rowIndex = $this->readSsiPrototypePrescriptionExcel($prescriptionResults, $rowIndex, $sheet, $endColumn);
        $sheet->setCellValue('A'. $rowIndex, '■包材情報');
        $sheet->getStyle('A' . $rowIndex)->applyFromArray(array('font' => array('bold' => true)));
        $rowIndex += 2;
        $tmp =  $rowIndex + 1;

        $this->mergePkgInfoCell($sheet, $rowIndex);
        $sheet->getStyle('C'. $rowIndex . ':' . $endColumn . $rowIndex)->applyFromArray(
            $this->getHeaderStyle()
        );
        $this->setPkgInfoTitle($sheet, $rowIndex);
        $sheet->setCellValue('D'. $tmp, '合計');

        $this->setPrototypePkgInfoExcel($prototypePkgInfo, $rowIndex, $sheet, $endColumn);

        $sheet->setTitle('出力');
        $bookFileName = sprintf("%06d", $data['case_management_no']) . '-' . sprintf("%03d", $data['est_branch_no']) .'-'.sprintf("%03d", $data['prototype_request_branch_no']) . '-' . sprintf("%03d", $data['prototype_result_branch_no']) . '_' .date('YmdHis');
        $writer = PHPExcel_IOFactory::createWriter($book, 'Excel2007');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment;filename="'.$bookFileName.'.xlsx"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');

        exit;
    }

    public function readSsiPrototypePrescriptionExcel($ssiPrototypePrescriptions, $rowIndex, $sheet, $endColumn) {
        foreach ($ssiPrototypePrescriptions as $key => $ssiPrototypePrescription) {
            $this->setPrototypePrescriptionsTableTitle($sheet, $rowIndex, $key);

            $rowIndex++;
            $tmp =  $rowIndex + 1;
            $this->mergePrototypePrescriptionsCell($sheet, $rowIndex, $key);
            $this->setPrototypePrescriptionsTitle($sheet, $rowIndex, $key);
            $sheet->getStyle('C'. $rowIndex . ':' . $endColumn . $rowIndex)->applyFromArray(
                $this->getHeaderStyle()
            );
            $sheet->setCellValue('D'. $tmp, '合計');
            $rowIndex = $this->setSsiPrototypePrescriptionExcel($ssiPrototypePrescription, $rowIndex, $sheet, $endColumn, $key);

            $rowIndex += 2;
        }

        return $rowIndex;
    }

    private function setSsiPrototypePrescriptionExcel($ssiPrototypePrescription, $rowIndex, $sheet, $endColumn, $key) {
        foreach ($ssiPrototypePrescription as $stt => $value) {
            $rowIndex++;
            $this->mergePrototypePrescriptionsCell($sheet, $rowIndex, $key);
            $sheet->getStyle('C'. $rowIndex . ':' . $endColumn . $rowIndex)->applyFromArray(
                $this->getCellStyle()
            );
            $sheet->getStyle('C'. $rowIndex . ':C' . $rowIndex )->applyFromArray(
                $this->getHeaderStyle()
            );

            if($stt == 0 ) {
                $stt = null;
            }

            $sheet->setCellValue('C'. $rowIndex, $stt);
            $sheet->setCellValue('E'. $rowIndex, $value['item_cd']);
            $sheet->setCellValue('F'. $rowIndex, $value['est_item_general_name']);
            $sheet->setCellValue('G'. $rowIndex, $value['estimate_normal_name']);
            $sheet->setCellValue('H'. $rowIndex, $value['est_item_name']);
            $sheet->setCellValue('I'. $rowIndex, $value['est_use_unit_price']);
            $sheet->setCellValue('J'. $rowIndex, $value['maker_name']);
            $sheet->setCellValue('K'. $rowIndex, $value['supplier_name']);
            $sheet->setCellValue('L'. $rowIndex, $value['est_item_tehai_name']);
            $sheet->setCellValue('M'. $rowIndex, $value['order_unit']);
            $sheet->setCellValue('N'. $rowIndex, $value['est_item_tehai_spec_name']);
            $sheet->setCellValue('O'. $rowIndex, $value['blendinig_ratio']);
            $sheet->setCellValue('P'. $rowIndex, $value['display_unit']);
            $sheet->setCellValue('Q'. $rowIndex, $value['act_qty']);
            $sheet->setCellValue('R'. $rowIndex, $value['provided_qty']);
            $sheet->setCellValue('S'. $rowIndex, $value['provided_spc']);
            $sheet->setCellValue('T'. $rowIndex, $value['provided_date']);
            $sheet->setCellValue('U'. $rowIndex, $value['note']);
        }

        return $rowIndex;
    }

    private function setPrototypePkgInfoExcel($prototypePkgInfo, $rowIndex, $sheet, $endColumn) {
        foreach($prototypePkgInfo as $key => $value) {
            $rowIndex ++;
            $this->mergePkgInfoCell($sheet, $rowIndex);
            $sheet->getStyle('C'. $rowIndex . ':'. $endColumn . $rowIndex)->applyFromArray(
                $this->getCellStyle()
            );
            $sheet->getStyle('C'. $rowIndex . ':C' . $rowIndex )->applyFromArray(
                $this->getHeaderStyle()
            );

            if ($key == 0) {
                $key = null;
            }

            $sheet->setCellValue('C'. $rowIndex, $key);
            $sheet->setCellValue('E'. $rowIndex, $value['item_cd']);
            $sheet->setCellValue('G'. $rowIndex, $value['item_name']);
            $sheet->setCellValue('I'. $rowIndex, $value['item_kigou']);
            $sheet->setCellValue('K'. $rowIndex, $value['use_unit_price']);
            $sheet->setCellValue('M'. $rowIndex, $value['maker_name']);
            $sheet->setCellValue('O'. $rowIndex, $value['order_destination_name']);
            $sheet->setCellValue('Q'. $rowIndex, $value['item_tehai_name']);
            $sheet->setCellValue('R'. $rowIndex, $value['item_tehai_spec_name']);
            $sheet->setCellValue('S'. $rowIndex, $value['quantity']);
            $sheet->setCellValue('T'. $rowIndex, $value['tot_amt']);
            $sheet->setCellValue('U'. $rowIndex, $value['est_item_note']);
        }

        return $rowIndex;
    }
}
