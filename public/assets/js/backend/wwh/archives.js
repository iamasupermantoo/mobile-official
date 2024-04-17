define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'wwh/archives/index' + location.search,
                    add_url: 'wwh/archives/add',
                    edit_url: 'wwh/archives/edit',
                    del_url: 'wwh/archives/del',
                    multi_url: 'wwh/archives/multi',
                    import_url: 'wwh/archives/import',
                    table: 'wwh_archives',
                    dragsort_url:'',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'weigh',
                fixedColumns: true,
                fixedRightNumber: 1,
                columns: [
                    [
                        {
                            checkbox: true, formatter:function (value,row,index){
                                if (row.status == 1){
                                    return{
                                        disabled:true
                                    };
                                }
                            }
                        },
                        {field: 'id', title: __('Id')},
                        {
                            field: 'column_id',
                            title: __('Column_id'),
                            visible: false,
                            addclass: 'selectpage',
                            extend: 'data-source="wwh/column/index" data-field="name"',
                            operate: 'in',
                            formatter: Table.api.formatter.search
                        },
                        {
                            field: 'column.name',
                            title: __('Column'),
                            operate: false,
                            formatter: function (value, row, index) {
                                return '<a href="javascript:;" class="searchit" data-field="column_id" data-value="' + row.column_id + '">' + value + '</a>';
                            }
                        },
                        {
                            field: 'title', title: __('Title'), align: 'left', operate: 'like', customField: 'rec_data', formatter: function (value, row, index) {
                                var flagObj = $.extend({}, this, {searchList: {"0":__('Rec_data 0'),"1":__('Rec_data 1'),"10":__('Rec_data 10')},custom: {0: 'primary', 1: 'success', 10: 'warning'}});
                                if (row.rec_data == 0){
                                    return '<div class="archives-title"><a href="' + row.url + '" target="_blank"><span style="color:' + (row.style_color ? row.style_color : 'inherit') + ';font-weight:' + (row.style_bold ? 'bold' : 'normal') + '">' + value + '</span></a></div>';
                                } else {
                                    return '<div class="archives-title"><a href="' + row.url + '" target="_blank"><span style="color:' + (row.style_color ? row.style_color : 'inherit') + ';font-weight:' + (row.style_bold ? 'bold' : 'normal') + '">' + value + '</span></a></div>' +
                                        '<div class="archives-label">' + Table.api.formatter.flag.call(flagObj, row['rec_data'], row, index) + '</div>';
                                }
                            }
                        },
						{field: 'rec_data', title: __('Rec_data'), operate: 'find_in_set', visible: false, searchList: Config.recDataList, formatter: Table.api.formatter.flag},
                        {field: 'image', title: __('Image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {
                            field: 'weigh',
                            title: __('Weigh'),
                            formatter: function (value, row, index) {
                                return '<input type="text" class="form-control text-center text-weigh" data-id="' + row.id + '" value="' + value + '" style="width:50px;margin:0 auto;" />';
                            },
                            events: {
                                "dblclick .text-weigh": function (e) {
                                    e.preventDefault();
                                    e.stopPropagation();
                                    return false;
                                }
                            }
                        },
                        {field: 'views', title: __('Views')},
                        {field: 'time', title: __('Time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false},
                        {field: 'status', title: __('Status'), searchList: {"0":__('Status 0'),"1":__('Status 1')}, custom: {0: 'grey', 1: 'info'}, formatter: Table.api.formatter.flag},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'faudit',
                                    text: __('反审核'),
                                    title: __('反审核'),
                                    classname: 'btn btn-xs btn-danger btn-magic btn-ajax',
                                    // icon: 'fa fa-close',
                                    url: 'wwh/archives/faudit',
                                    hidden:function(row){
                                        if(row.status != '1'){
                                            return true;
                                        }
                                    },
                                    success: function (data, ret) {
                                        table.bootstrapTable('refresh');    //自动刷新
                                    },
                                }
                            ],
                            formatter: function (value, row, index) {
                                var that = $.extend({}, this);
                                var table = $(that.table).clone(true);
                                if (row.status == 1){
                                    $(table).data("operate-edit",null);
                                    $(table).data("operate-del",null);
                                }
                                that.table = table;
                                return Table.api.formatter.operate.call(that, value, row, index);
                            }}
                    ]
                ]
            });

            $(document).on("change", ".text-weigh", function () {
                $(this).data("params", {weigh: $(this).val()});
                Table.api.multi('', [$(this).data("id")], table, this);
                return false;
            });

            //审核
            $(document).on("click", ".btn-audit", function () {
                var data = table.bootstrapTable('getSelections');
                var ids = [];
                if (data.length === 0) {
                    Toastr.error("请选择操作信息");
                    return;
                }
                for (var i = 0; i < data.length; i++) {
                    ids[i] = data[i]['id']
                }
                Layer.confirm(
                    '确认选中'+ids.length+'条审核吗?',
                    {icon: 3, title: __('Warning'), offset: '40%', shadeClose: true},
                    function (index) {
                        Layer.close(index);
                        Backend.api.ajax({
                            url: "wwh/archives/audit",
                            data: {ids:ids}
                        }, function(data, ret){//成功的回调
                            if (ret.code === 1) {
                                table.bootstrapTable('refresh');
                                Layer.close(index);
                            } else {
                                Layer.close(index);
                                Toastr.error(ret.msg);
                            }
                        }, function(data, ret){//失败的回调
                            console.log(ret);
                            Layer.close(index);
                        });
                    }
                );
            });

            // 为表格绑定事件
            Table.api.bindevent(table);

            table.on('post-body.bs.table',function(e, settings, json, xhr){
                $(".btn-add").data("area", ['80%','80%']);
                $(".btn-editone").data("area", ['80%','80%']);
            });

            $(document).on("click", "a.btn-channel", function () {
                $("#archivespanel").toggleClass("col-md-9", $("#channelbar").hasClass("hidden"));
                $("#archivespanel").toggleClass("col-full-width", !$("#channelbar").hasClass("hidden"));
                $("#channelbar").toggleClass("hidden");
            });

            require(['jstree'], function () {
                //全选和展开
                $(document).on("click", "#checkall", function () {
                    $("#channeltree").jstree($(this).prop("checked") ? "check_all" : "uncheck_all");
                });
                $(document).on("click", "#expandall", function () {
                    $("#channeltree").jstree($(this).prop("checked") ? "open_all" : "close_all");
                });
                $('#channeltree').on("changed.jstree", function (e, data) {
                    $(".commonsearch-table input[name=column_id]").val(data.selected.join(","));
                    table.bootstrapTable('refresh', {});
                    return false;
                });
                $('#channeltree').jstree({
                    "themes": {
                        "stripes": true
                    },
                    "checkbox": {
                        "keep_selected_style": false,
                    },
                    "types": {
                        "channel": {
                            "icon": false,
                        },
                        "list": {
                            "icon": false,
                        },
                        "link": {
                            "icon": false,
                        },
                        "disabled": {
                            "check_node": false,
                            "uncheck_node": false
                        }
                    },
                    'plugins': ["types", "checkbox"],
                    "core": {
                        "multiple": true,
                        'check_callback': true,
                        "data": Config.columnList
                    }
                });
            });
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
            $("input[name='row[type]']:checked").trigger("fa.event.typeupdated", "edit");
        },
        api: {
            formatter: {
                subnode: function (value, row, index) {
                    return '<a href="javascript:;" data-toggle="tooltip" title="' + __('Toggle sub menu') + '" data-id="' + row.id + '" data-parent_id="' + row.parent_id + '" class="btn btn-xs '
                        + (row.haschild == 1 || row.ismenu == 1 ? 'btn-success' : 'btn-default disabled') + ' btn-node-sub"><i class="fa fa-' + (row.haschild == 1 || row.ismenu == 1 ? 'sitemap' : 'list') + '"></i></a>';
                }
            },
            bindevent: function () {
                //根据类型显示隐藏对应字段
                $(document).on("click fa.event.typeupdated", "input[name='row[type]']", function (e, ref) {
                    $(".tf").addClass("hidden");
                    $(".tf.tf-" + $(this).val()).removeClass("hidden");
                });
                //不验证隐藏元素
                $('form[role=form]').data("validator-options", {
                    ignore: ':hidden'
                });

                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
