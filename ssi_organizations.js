$(document).ready(function(event){
    $("#support_organization_cd").prepend("<option value=''>選択ください</option>").val('');

    $("#add_new_organizations").click(function(){
        $("#add_organization").modal('show');
        $(".dlg_append_reg").hide();
        $(".dlg_skip_review_flg").hide();
        $('#dlg_error_append_add').hide();
    });

    $(".close-modal").click(function(){
        $("#update_organization").modal('hide');
    });

    $("#reader_all_organizations").click(function(){
        $("#upload_csv").modal('show');
    });

    $('#btn-cancel').click(function(){
        if (confirm('変更の内容を破壊されます。よろしいですか？')) {
            $("#update_organization").modal('hide');
        }
    });

    $('#close-cancel_modal').click(function() {
        $('#cancel_organization').modal('hide');
    });

    $(document).on('click', "#btn_upload_csv", function(e) {
        var fileupload = document.getElementById("organizations_csv");
        fileupload.click();
        $('#dlg_error_upload').hide();
    });

    $("input[name='organizations_csv']").on('change', function (){
        var fileName = this.value.split('\\')[this.value.split('\\').length - 1];
        $("#spnFilePath").html("<b>Selected File: </b>" + fileName);
    });

    var ssiOrganizationsTable =  $('#datatable-list_ssiOrganizations').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url" : 'ssi-organizations/loadData',
            "type": 'post',
        },
        "destroy": true,
        "language": {
            "processing": "<i class='fa fa-spinner fa-spin fa-3x fa-fw'></i>",
            "infoEmpty": "",
            "emptyTable": "データがありません。",
        },
        "order": [[ 3, "desc" ]],
        "autoWidth": false,
        "columns": [
            { "mData": "id"
            },
            { "mData": "organization_cd"
            },
            { "mData": "organization_name", 'mRender' : function (data, type, full) {
                return replaceHtmlCode(data);
                }
            },
            { "mData": "address1",
            },
            {
                "mData": null, "orderable": false , "searchable": false,
                "mRender": function (data, type, full) {
                    var content = "";

                    content += "<button type='button' class='btn btn-xs btn-warning btn-edit-organizations' data-organization_id='" + full.id + "'>編集</button>";
                    content += "<button type='button' class='btn btn-xs btn-danger btn-devare-organizations' data-organization_id='" + full.id + "'>削除</button>";
                    return content;
                }
            },
        ]
    });

    $('#datatable-list_ssiOrganizations').on('click', ".btn-devare-organizations", function (e) {
        e.preventDefault();

        var id = $(this).data("organization_id");
        var data = {
            'id' : id,
        };

        if (confirm('この部署を削除します。よろしいですか？')) {
            $.ajax({
                type: 'POST',
                url: 'ssi-organizations/remove',
                data: data,
                success: function(response)
                {
                    ssiOrganizationsTable.ajax.reload(null, false);
                },
            });
        }
    });

    $('#datatable-list_ssiOrganizations').on('click', ".btn-edit-organizations", function (e) {
        var id = $(this).data("organization_id");
        var data = {
            'id' : id,
        };
        $.ajax({
                type: 'POST',
                url: 'ssi-organizations/getEdit',
                data: data,
                success: function(response)
                {
                    setFormValues(response);
                    $("#update_organization").modal('show');
                    $('#dlg_error_append').hide();
                },
            });
    });

    $(document).on('change', '.appending_flg', function(){
        if( $(this).is(":checked") ){
            $(".appending_flg").val('1');
            $("#appending_grp").val('1');
            $(".dlg_append_reg").show();
        }
        else {
            $('.dlg_append_reg').hide();
        }

    });

    $(document).on('change', '.review_flg', function(){
        if( $(this).is(":checked") ){
            $(".review_flg").val('1');
        }
        else {
            $('.review_flg').val('0');
        }

    });

    $(document).on('change', '.skip_review_flg', function(){
        if( $(this).is(":checked") ){
            $(".skip_review_flg").val('1');
        }
        else {
            $('.skip_review_flg').val('0');
        }

    });

    $(document).on('change', '.organization_type', function(){
        var selected =this.value;
        
        if(selected == 3){
            $(".dlg_skip_review_flg").show();
        }
        else {
            $(".dlg_skip_review_flg").hide();
        }
    });

    function setSelect2() {
        $('.js-example-basic-select2').select2({
            multiple: false,
            width : '100.0%',
            matcher: matchStart,
            language: {
                "noResults": function(){
                    return "部署が見つかりません";
                }
            }
        });
    }
    
    setSelect2();

    function setFormValues(response) {
        $("#update_organization #organization_type").val(response.organization_type);
        $("#update_organization #organization_cd").val(response.organization_cd);
        $("#update_organization #organization_name").val(response.organization_name);
        $("#update_organization #postal_code").val(response.postal_code);
        $("#update_organization #address1").val(response.address1);
        $("#update_organization #address2").val(response.address2);
        $("#update_organization #tel").val(response.tel);
        $("#update_organization #fax").val(response.fax);
        $("#update_organization #support_organization_cd").val(response.support_organization_cd).trigger('change');
        $("#update_organization #email").val(response.email);

        if(response.review_flg == true) {
            $("#update_organization #review_flg").val(response.review_flg).prop( "checked", true );
        }
        else {
            $("#update_organization #review_flg").val(response.review_flg).prop( "checked", false );
        }

        if(response.appending_flg == true) {
            $("#update_organization #appending_flg").val(response.appending_flg).prop( "checked", true );
            $('#update_organization .dlg_append_reg').show();
        }
        else {
            $("#update_organization #appending_flg").val(response.appending_flg).prop( "checked", false );
            $('#update_organization .dlg_append_reg').hide();
        }

        if(response.organization_type == 3) {
            if(response.skip_review_flg == true) {
                $(".skip_review_flg").val(response.skip_review_flg).prop( "checked", true );
            }
            else {
                $(".skip_review_flg").val(response.skip_review_flg).prop( "checked", false );
            }

            $('.dlg_skip_review_flg').show();
        }
        else {
            $('.dlg_skip_review_flg').hide();
        }

        $("#update_organization #appending_grp").val(response.appending_grp);
        $("#update_organization #support_organization_cd option[value="+response.organization_cd+"]").attr('disabled','disabled');
        setSelect2();
    }

    $('#update_organization').on('hide.bs.modal', function(e) {
        $("#update_organization").find("input, textarea, select")
        .val('')
        .end()
        .find("input[type=checkbox], input[type=radio]")
        .prop("checked", "")
        .end()
        .find("#organization_type").val('0').end();
        $('option[disabled]').prop("disabled", false);
        $('#dlg_error_append').hide();
    });

    $("#upload_csv").on('hide.bs.modal', function(e) {
        $('#upload_csv').off('click');
        $('#dlg_error_upload').hide();
        $('#add_csv').off('click');
    });

    $('#appending_grp').on('change', function(e) {
        $('#dlg_error_append').hide();
    });

    $('#appending_flg').on('change', function(e) {
        if (!$(this).is(':checked')) {
            $('#dlg_error_append').hide();
        }
    })

    $('#form_organizations_update').on('submit', function(event) {
        event.preventDefault();

        if ($(this).valid()) {
            var isChecked = $('#appending_flg').is(':checked');
            var appending_grp = $('#appending_grp').val();
            var data = $(this).serializeArray();

            if (!isChecked || (isChecked && appending_grp)) {
                $.ajax({
                    type: 'POST',
                    url: "ssi-organizations/store",
                    data: data ,
                    success: function (response) {
                        ssiOrganizationsTable.ajax.reload(null, false);
                        $("#support_organization_cd option[value="+data[1]['value']+"]").text(data[2]["value"]);
                        $("#support_organization_cd_1 option[value="+data[1]['value']+"]").text(data[2]["value"]);
                        setSelect2();
                        alert('成功した!');
                        $("#update_organization").modal('hide');
                    },
                    error: function (err, text, throwErr) {
                    }
                });
            }
            else {
                $('#dlg_error_append').show();
            }
        }
    });

    $('#add_organization').on('hide.bs.modal', function(e) {
        $("#add_organization").find("input, textarea")
        .val('')
        .end()
        .find("input[type=checkbox], input[type=radio]")
        .prop("checked", false)
        .end()
        .find("#organization_type").val('0').end()
        .find("#appending_grp").val('1').end();
        $('.dlg_append_reg').hide();
        $("#support_organization_cd_1").val('').trigger('change')
    });

    $('#form_organizations_add').on('submit', function(event) {
        event.preventDefault();

        if ($(this).valid()) {
            var isChecked = $('#appending_flg').is(':checked');
            var appending_grp = $('#appending_grp').val();
            var data =  $(this).serializeArray();
            
            if (!isChecked || (isChecked && appending_grp)) {
                $.ajax({
                    type: 'POST',
                    url: "ssi-organizations/add",
                    data: data ,
                    success: function (response) {
                        ssiOrganizationsTable.ajax.reload();
                        ssiOrganizationsTable.page('last').draw('page');
                        alert('成功した');
                        $("#support_organization_cd_1").append("<option value='"+data[1]['value']+"'>"+data[2]['value']+"</option>");
                        $("#support_organization_cd").append("<option value='"+data[1]['value']+"'>"+data[2]['value']+"</option>");
                        setSelect2();
                        $("#add_organization").modal('hide');
                    },
                    error: function (err, text, throwErr) {
                        alert('部署コードが存在しました。再度確認ください。')
                    }
                });

            }
            else {
                $('#dlg_error_append').show();
            }
        }
    });

    $('#form_upload_csv').on('submit',function(e) {
        e.preventDefault();

        if ($("input[type=file]").valid()) {
            var fd = new FormData();
            fd.append('organizations_csv', $("input[type=file]").prop('files')[0]);

            $.ajax({
                type: 'POST',
                url: "ssi-organizations/import",
                cache: false,
                contentType: false,
                processData: false,
                data: fd,
                dataType: 'json',
                success: function (response) {
                    if (response) {
                        var data_new = response.data_new;
                        var data_edit = response.data_edit;
                        
                        if ( response.flag == true) {
                            ssiOrganizationsTable.ajax.reload();
                            ssiOrganizationsTable.page('last').draw('page');
                            alert(response.messages);
                            
                            for($i = 0; $i < data_new.length; $i ++) {
                                $("#support_organization_cd_1").append("<option value='"+data_new[$i].organization_cd+"'>"+data_new[$i].organization_name+"</option>");
                                $("#support_organization_cd").append("<option value='"+data_new[$i].organization_cd+"'>"+data_new[$i].organization_name+"</option>");
                            }

                            for($i = 0; $i < data_edit.length; $i ++) {
                                $("#support_organization_cd option[value="+data_edit[$i].organization_cd+"]").text(data_edit[$i].organization_name);
                                $("#support_organization_cd_1 option[value="+data_edit[$i].organization_cd+"]").text(data_edit[$i].organization_name);
                            }

                            $("#upload_csv").modal('hide');
                        }
                        else{
                            alert(response.messages);
                            $("#upload_csv").modal('hide');
                        }

                        return;
                    }
                },
            });
            return;
        }

        $('#dlg_error_upload').show();
    });

    $('#form_upload_csv').validate({
        rules: {
            "organizations_csv" : {
                required: true,
            },
        },
        messages: {
            "organizations_csv" : {
                required: "ファイルがありません。",
            }
        }
    });

    $('#form_organizations_update').validate({
        rules: {
            "organization_name" : {
                required: true,
            },
        },
        messages: {
            "organization_name" : {
                required: "部署名を入力して下さい。"
            }
        }
    });

    $('#form_organizations_add').validate({
        rules: {
            "organization_name" : {
                required: true,
            },
            "organization_cd" : {
                required: true,
            },
        },
        messages: {
            "organization_name" : {
                required: "部署名を入力して下さい。"
            },
            "organization_cd" : {
                required: "部署コードを入力して下さい。"
            }
        }
    });

    function matchStart(params, data) {
        if ($.trim(params.term) === '') {
            return data;
        }

        if (data.text.indexOf(params.term) > -1 ||  data.id.indexOf(params.term) > -1) {
            return $.extend({}, data, true);
        }

        return null;
    }
});
