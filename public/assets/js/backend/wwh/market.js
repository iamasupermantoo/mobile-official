define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'wwh/market/index' + location.search,
                    add_url: 'wwh/market/add',
                    edit_url: 'wwh/market/edit',
                    del_url: 'wwh/market/del',
                    multi_url: 'wwh/market/multi',
                    import_url: 'wwh/market/import',
                    table: 'wwh_market',
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
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'citylist', title: __('Citylist'), operate: 'LIKE'},
                        {field: 'address', title: __('Address'), operate: 'LIKE'},
                        {field: 'sale', title: __('Sale'), operate: 'LIKE'},
                        {field: 'tech', title: __('Tech'), operate: 'LIKE'},
                        {field: 'mail', title: __('Mail'), operate: 'LIKE'},
                        {field: 'fax', title: __('Fax'), operate: 'LIKE'},
                        {field: 'updatetime', title: __('Updatetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
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
