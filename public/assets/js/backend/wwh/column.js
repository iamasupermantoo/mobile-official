define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    //设置弹窗宽高
    Fast.config.openArea = ['80%', '80%'];

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'wwh/column/index',
                    add_url: 'wwh/column/add',
                    edit_url: 'wwh/column/edit',
                    del_url: 'wwh/column/del',
                    multi_url: 'wwh/column/multi',
                    dragsort_url: '',
                    table: 'wwh_column',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'weigh',
                pagination: false,
                escape: false,
                fixedColumns: true,
                fixedRightNumber: 1,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {
                            field: 'id',
                            title: '<a href="javascript:;" class="btn btn-success btn-xs btn-toggle"><i class="fa fa-chevron-up"></i></a>',
                            operate: false,
                            formatter: Controller.api.formatter.subnode
                        },
                        {field: 'classify', title: __('Classify'), operate: false, searchList: Config.classifyList,
                            formatter: Table.api.formatter.flag
                        },
                        {field: 'name', title: __('Name'), align: 'left'},
                        {
                            field: 'url', title: __('Url'), formatter: function (value, row, index) {
                                return '<a href="' + value + '" target="_blank" class="btn btn-default btn-xs"><i class="fa fa-link"></i></a>';
                            }
                        },
                        // {field: 'diyname', title: __('Diyname'), operate: 'LIKE'},
                        {
                            field: 'template_name',
                            title: __('template_name'),
                            width: 100,
                            formatter:function (value,row,index){
                                if(row['template_id']!==0){
                                    return '<span class="label label-warning">'+value+'</span>';
                                } else {
                                    return '<span class="label label-primary">'+value+'</span>';
                                }
                            }
                        },
                        {field: 'listtpl', title: __('Listtpl'), operate: 'LIKE'},
                        {field: 'showtpl', title: __('Showtpl'), operate: 'LIKE'},
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
                        {field: 'isnav', title: __('Isnav'), searchList: {"1": __('Yes'), "0": __('No')}, formatter: Table.api.formatter.toggle},
                        {field: 'status', title: __('Status'), operate: false, formatter: Table.api.formatter.status},
                        {
                            field: 'operate',
                            title: __('Operate'),
                            clickToSelect: false,
                            table: table,
                            width: 170,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate,
                            buttons: [
                                {
                                    name: 'addsub',
                                    text: '添加子栏目',
                                    classname: 'btn btn-primary btn-xs btn-dialog',
                                    icon: 'fa fa-plus',
                                    url: 'wwh/column/add/parent_id/{ids}'
                                }
                            ]
                        }
                    ]
                ],
                search: false,
                commonSearch: false
            });

            $(document).on("change", ".text-weigh", function () {
                $(this).data("params", {weigh: $(this).val()});
                Table.api.multi('', [$(this).data("id")], table, this);
                return false;
            });

            //当内容渲染完成后
            table.on('post-body.bs.table', function (e, settings, json, xhr) {
                //默认隐藏所有子节点
                //$("a.btn[data-id][data-parent_id][data-parent_id!=0]").closest("tr").hide();
                //$(".btn-node-sub.disabled[data-parent_id!=0]").closest("tr").hide();

                //显示隐藏子节点
                $(".btn-node-sub").off("click").on("click", function (e) {
                    var status = $(this).data("shown") || $("a.btn[data-parent_id='" + $(this).data("id") + "']:visible").size() > 0 ? true : false;
                    $("a.btn[data-parent_id='" + $(this).data("id") + "']").each(function () {
                        $(this).closest("tr").toggle(!status);
                        if (!$(this).hasClass("disabled")) {
                            $(this).trigger("click");
                        }
                    });
                    $(this).data("shown", !status);
                    return false;
                });

            });
            //展开隐藏一级
            $(document.body).on("click", ".btn-toggle", function (e) {
                $("a.btn[data-id][data-parent_id][data-parent_id!=0].disabled").closest("tr").hide();
                var that = this;
                var show = $("i", that).hasClass("fa-chevron-down");
                $("i", that).toggleClass("fa-chevron-down", !show);
                $("i", that).toggleClass("fa-chevron-up", show);
                $("a.btn[data-id][data-parent_id][data-parent_id!=0]").closest("tr").toggle(show);
                $(".btn-node-sub[data-parent_id=0]").data("shown", show);
            });
            //展开隐藏全部
            $(document.body).on("click", ".btn-toggle-all", function (e) {
                var that = this;
                var show = $("i", that).hasClass("fa-plus");
                $("i", that).toggleClass("fa-plus", !show);
                $("i", that).toggleClass("fa-minus", show);
                $(".btn-node-sub.disabled[data-parent_id!=0]").closest("tr").toggle(show);
                $(".btn-node-sub[data-parent_id!=0]").data("shown", show);
            });

            // 为表格绑定事件
            Table.api.bindevent(table);

            table.on('post-body.bs.table',function(e, settings, json, xhr){
                $(".btn-add").data("area", ['80%','80%']);
                $(".btn-editone").data("area", ['80%','80%']);
            });
        },
        add: function () {
            Controller.api.bindevent();
            $("input[name='row[type]'][value=list]").trigger("click");
            $("select[name='row[template_id]']").trigger("change");
        },
        edit: function () {
            Controller.api.bindevent();
            $("input[name='row[type]']:checked").trigger("fa.event.typeupdated", "edit");
        },
        api: {
            events: {
                operate: {
                    'click .btn-addson': function (e, value, row, index) {
                        e.stopPropagation();
                        e.preventDefault();
                        var table = $(this).closest('table');
                        var options = table.bootstrapTable('getOptions');
                        var ids = row[options.pk];
                        row = $.extend({}, row ? row : {}, {ids: ids});
                        var url = $(this).attr('href');
                        Fast.api.open(Table.api.replaceurl(url, row, table), $(this).data("original-title") || $(this).attr("title") || __('Edit'), $(this).data() || {});
                    },
                }
            },
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
                $(document).on("change", "#c-select-tpl", function () {
                    $("#c-tpl").val($(this).val());
                });
                //不验证隐藏元素
                $('form[role=form]').data("validator-options", {
                    ignore: ':hidden'
                });

                Form.api.bindevent($("form[role=form]"));
                $(document).on("change", "select[name='row[template_id]']", function () {
                    var data = $("option:selected", this).data();
                    var type = $("input[name='row[type]']:checked").val();
                    $("input[name='row[listtpl]']").val(data.listtpl).prev().val(data.listtpl);
                    $("input[name='row[showtpl]']").val(data.showtpl).prev().val(data.showtpl);
                });
            }
        }
    };
    return Controller;
});
