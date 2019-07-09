<!-- Modal -->
<style>
.required {
        color: red;
}
.center {
	margin-top:9px;
}	

.span {
	margin-left: 10px;
	position:relative;
}
#update_organization input.error{
	color: #555 !important;
} 

#add_organization input.error{
	color: #555 !important;
}

input[type=checkbox] {
        position: relative;
	    cursor: pointer;
		margin-top: 8px;
}
input[type=checkbox]:before {
	content: "";
	display: block;
	position: absolute;
	width: 16px;
	height: 16px;
	top: 0;
	left: 0;
	border-radius: 2px;
	background-color: grey;
}
input[type=checkbox]:checked:after {
	content: "";
	display: block;
	width: 5px;
	height: 10px;
	border: solid white;
	border-width: 0 2px 2px 0;
	-webkit-transform: rotate(45deg);
	-ms-transform: rotate(45deg);
	transform: rotate(45deg);
	position: absolute;
	top: 2px;
	left: 6px;
}

</style>

<div class="modal " id="update_organization" role="dialog" aria-labelledby="myModalLabel" >
	<div class="modal-dialog modal-lg">
		<form action="#" method="POST" id="form_organizations_update">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close close-modal" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title">部署編集</h4>
				</div>
				<div class="modal-body">
					<div class="row form-group">
						<label class="col-xs-2 control-label text-right center">部署種類<span class="required center">*</span></label>
						<div class="input select col-xs-10">
							<select class="form-control organization_type" id="organization_type" name="organization_type">
								<option value="0">通常</option>
								<option value="1">情報システム</option>
								<option value="2">庶務</option>
								<option value="3">受注管理</option>
							</select>
						</div>
					</div>
					<div class="row form-group">
						<label class="col-xs-2 control-label text-right center">部署コード<span class="required centercenter">*</span></label>
						<div class="col-xs-10" style="text-align: left;">
							<input type="text" name="organization_cd" class="form-control" id="organization_cd" value="" readonly>
						</div>
					</div>
					<div class="row form-group">
						<label class="col-xs-2 control-label text-right center">部署名<span class="required center">*</span></label>
						<div class="col-xs-10" style="text-align: left;">
							<input type="text" name="organization_name" class="form-control" id="organization_name" value=""/>
						</div>
					</div>
					<div class="row form-group">
						<label class="col-xs-2 control-label text-right center">郵便番号</label>
						<div class="col-xs-10" style="text-align: left;">
							<input type="text" name="postal_code" class="form-control" id="postal_code" value=""/>
						</div>
					</div>
					<div class="row form-group">
						<label class="col-xs-2 control-label text-right center">住所１</label>
						<div class="col-xs-10" style="text-align: left;">
							<input type="text" name="address1" class="form-control" id="address1" value=""/>
						</div>
					</div>
					<div class="row form-group">
						<label class="col-xs-2 control-label text-right center">住所 2</label>
						<div class="col-xs-10" style="text-align: left;">
							<input type="text" name="address2" class="form-control" id="address2" value=""/>
						</div>
					</div>
					<div class="row form-group">
						<label class="col-xs-2 control-label text-right center">電話番号</label>
						<div class="col-xs-10" style="text-align: left;">
							<input type="text" name="tel" class="form-control" id="tel" value=""/>
						</div>
					</div>
					<div class="row form-group">
						<label class="col-xs-2 control-label text-right center">FAX</label>
						<div class="col-xs-10" style="text-align: left;">
							<input type="text" name="fax" class="form-control" id="fax" value=""/>
						</div>
					</div>
					<div class="row form-group">
						<label class="col-xs-2 control-label text-right center">庶務部署</label>
						<div class="col-xs-10">
						<?= $this->Form->select(
                            'support_organization_cd',
                            $organizations
							,[
								'id' => 'support_organization_cd',
								'class' => 'form-control js-example-basic-select2',
								'label' => false,
							]); ?>
						</div>
					</div>
					<div class="row form-group">
						<label class="col-xs-2 control-label text-right center">メールアドレス</label>
						<div class="col-xs-10" style="text-align: left;">
							<input type="text" name="email" class="form-control" id="email" value=""/>
						</div>
                    </div>
                    <div class="row form-group">
						<div class="col-xs-3 text-center">
							<input type="checkbox" name="review_flg" id="review_flg" value="1" checked><span class="span">確認部門</span>
						</div>
						<div class="col-xs-3 text-center">
							<input class="appending_flg" type="checkbox" name="appending_flg" id="appending_flg" value="1" checked><span class="span">追記部門</span>
						</div>
						<div class="dlg_append_reg" id="dlg_append_reg">
							<label class="col-xs-2 control-label text-right center" id="txt_appending_grp">追記種類</label>
							<div class="input select col-xs-4" id="div_appending_grp">
								<select class="form-control" id="appending_grp" name="appending_grp">
									<option value="1">開発</option>
									<option value="2">生菅</option>
									<option value="3">購買</option>
									<option value="4">品質</option>
								</select>
								<div id="dlg_error_append" class="invalid-feedback" style="color:red">
									追記種類を選択ください
								</div>
							</div>
						</div>
					</div>
					<div class="row form-group dlg_skip_review_flg">
						<div class="col-xs-3 text-center">
							<input class="skip_review_flg" type="checkbox" name="skip_review_flg" id="skip_review_flg"><span class="span">承認不要</span>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-danger" id ="btn-cancel">キャンセル</button>
					<button type="submit" class="btn btn-primary update_organizations">保存</button>
				</div>
			</div><!-- /.modal-content -->
		</form>
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->


