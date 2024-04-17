define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'wwh/injoin/index' + location.search,
                    add_url: 'wwh/injoin/add',
                    edit_url: 'wwh/injoin/edit',
                    del_url: 'wwh/injoin/del',
                    multi_url: 'wwh/injoin/multi',
                    import_url: 'wwh/injoin/import',
                    table: 'wwh_injoin',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
				fixedColumns: true,
                fixedRightNumber: 1,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'realname', title: __('Realname'), operate: 'LIKE'},
                        {field: 'tel', title: __('Tel'), operate: 'LIKE'},
                        {field: 'gangwei', title: __('Gangwei'), operate: 'LIKE'},
                        {field: 'content', title: __('Content'), operate: 'LIKE'},
                        {field: 'url', title: __('Url'), operate: 'LIKE', formatter: Table.api.formatter.url},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
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
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});