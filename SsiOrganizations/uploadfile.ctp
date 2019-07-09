<div class="modal fade" id="upload_csv" role="dialog" aria-labelledby="myModalLabel" >
	<div class="modal-dialog">
		<form action="ssi-organizations/import"  method="POST" id="form_upload_csv" enctype='multipart/form-data'>
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close close-modal" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title">一括登録</h4>
				</div>
				<div class="modal-body">
					<div class="row form-group" style="text-align:center">
                        <button type="button" class="btn btn-primary" id="btn_upload_csv" style=" padding:10px 50px;" >ファイルアップロード</button>
					</div>
                    <div id="dlg_error_upload" class="invalid-feedback" style="color:red; text-align:center; display: none;">
                        ファイルがありません。
					</div>
                    <div class="row form-group" style="text-align:center" id="upload">
                        <span id="spnFilePath"></span>
                        <input type="file" style="display: none" accept=".csv" id="organizations_csv" name="organizations_csv"/>
                    </div>
				</div>
				<div class="modal-footer">
					<div style="text-align:center">
						<input type="submit" class="btn btn-success" name="submit" style="font-size: 15px; padding:10px 30px;" value="登録"/>
					</div>
				</div>
			</div><!-- /.modal-content -->
		</form>
	</div><!-- /.modal-dialog -->
</div>
<!-- /.modal -->

