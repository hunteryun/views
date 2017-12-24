/**
 * vue app
 */
Vue.http.options.emulateJSON = true;

var App = new Vue({
  el: '#App',
  data: {
    view_name: '',
    view_machine_name: 'default',
    view_description: '',
    view_table: '',
    view_fields: [],
    view_filters: [],
    view_sorts: [],
    view_template: '',
    view_relation_table: '',
    view_pager: {
      type: 'display_all',
      display: 10,
      offset: 0
    },
    view_path: '',
    view_permissions: [],
    has_pager: false,
    tables: [],
    templates: [],
    template_content: '',
    fields: {},
    preview_result: '',
    create_new_template: 'new',
    overwrit_template: true,
    show_editor: true,
    temp_template: '',
    relationships: [],
    filters_list: [],
    filter_ops: [],
    new_filter_field: '',
    new_filter_op: 'equals',
    new_filter_value: '',
    new_sort_field: '',
    new_sort_value: 'asc',
    new_sort_date: 'second',
    select_field_filter_type: '',
    select_field_sort_type: '',
    editedFilter: null,
    editedSort: null,
    edit_filter_mode: false,
    edit_sort_mode: false,
    edit_machine_name: false,
    json_export: false,
    need_permission: false,
    permissions: []
  },
  watch: {
    view_name: function (newName) {
      this.getMachineName()
    }
  },
  mounted: function () {
    this.initTablesList();
    this.initFilterOpList();
    this.initTemplatesList();
  },
  computed: {
    canPreview: function () {
      return (!this.json_export || this.view_fields.length == 0) && this.template_content == '';
    }
  },
  methods: {
    initTablesList: function() {
      var vm = this;
      vm.$http.get(base_path+'admin/api/tables').then(function (response) {
          if (response.body.length == 0) {
            layer.alert('init error！', {icon: 5});
          } else {
            vm.tables = response.body;
          }
      }, function (response) {
          layer.alert('init error！', {icon: 5});
      });
    },
    initFilterOpList: function() {
      var vm = this;
      vm.$http.get(base_path+'admin/api/filter-ops').then(function (response) {
        if (response.body.length == 0) {
          layer.alert('init error！', {icon: 5});
        } else {
          vm.filters_list = response.body;
        }
      }, function (response) {
        layer.alert('init error！', {icon: 5});
      });
  	},
    initTemplatesList: function() {
      var vm = this;
      vm.$http.post(base_path+'admin/api/templates').then(function (response) {
        if (response.body.length != 0) {
          vm.templates = response.body;
        }
      }, function (response) {
        layer.alert('init error！', {icon: 5});
      });
  	},
    getMachineName: _.debounce(
      function () {
        var vm = this;
        vm.$http.post(base_path+'admin/api/machine-name', {name:vm.view_name}).then(function (response) {
          if (response.body.length != 0) {
            vm.view_machine_name = response.body;
          }
        }, function (response) {
          layer.alert('create error！', {icon: 5});
        });
      },
      // 这是我们为判定用户停止输入等待的毫秒数
      1000
    ),
    editMachineName: function(){
      var vm = this;
      vm.edit_machine_name = !vm.edit_machine_name;
    },
    addNewFilter: function(e) {
      e.preventDefault();
      var vm = this;
      var value = vm.new_filter_value && vm.new_filter_value.trim();
      if (vm.new_filter_field == '' || vm.new_filter_op == '' || !value) {
        layer.alert('Please select the filter field！', {icon: 5});
        return;
      }
      vm.view_filters.push({
        field: vm.new_filter_field,
        op: vm.new_filter_op,
        value: vm.new_filter_value.trim(),
        lable: vm.filter_ops[vm.new_filter_op].lable,
      })
  	},
    editFilter: function (filter, e) {
      e.preventDefault();
      var vm = this;
      vm.new_filter_field = filter.field;
      vm.new_filter_op = filter.op;
      vm.new_filter_value = filter.value;
      vm.editedFilter = filter;
      vm.edit_filter_mode = true;
    },
    doneEditFilter: function (e) {
      e.preventDefault();
      var vm = this;
      var value = vm.new_filter_value && vm.new_filter_value.trim();
      if (!value) {
        layer.alert('Please set the filter value', {icon: 5});
        return;
      }
      vm.view_filters.splice(vm.view_filters.indexOf(vm.editedFilter), 1, {
        field: vm.new_filter_field,
        op: vm.new_filter_op,
        value: vm.new_filter_value.trim(),
        lable: vm.filter_ops[vm.new_filter_op].lable
      });
      vm.edit_filter_mode = false;
    },
    cancelEditFilter: function (e) {
      e.preventDefault();
      var vm = this;
      vm.new_filter_field = '';
      vm.new_filter_op = '';
      vm.new_filter_value = '';
      vm.edit_filter_mode = false;
    },
    removeFilter: function (filter, e) {
      e.preventDefault();
      var vm = this;
      vm.view_filters.splice(vm.view_filters.indexOf(filter), 1);
    },
    addNewSort: function(e) {
      e.preventDefault();
      var vm = this;
      if (vm.new_sort_field == '' || vm.new_sort_value == '') {
        layer.alert('Please select the sort field！', {icon: 5});
        return;
      }
      vm.view_sorts.push({
        field: vm.new_sort_field,
        value: vm.new_sort_value,
        date: vm.select_field_sort_type ? vm.new_sort_date : '',
      })
    },
    editSort: function (sort, e) {
      e.preventDefault();
      var vm = this;
      vm.new_sort_field = sort.field;
      vm.new_sort_value = sort.value;
      vm.new_sort_date = sort.date;
      vm.editedSort = sort;
      vm.edit_sort_mode = true;
    },
    doneEditSort: function (e) {
      e.preventDefault();
      var vm = this;
      vm.view_sorts.splice(vm.view_sorts.indexOf(vm.editedSort), 1, {
        field: vm.new_sort_field,
        value: vm.new_sort_value,
        date: vm.select_field_sort_type ? vm.new_sort_date : '',
      });
      vm.edit_sort_mode = false;
    },
    cancelEditSort: function (e) {
      e.preventDefault();
      var vm = this;
      vm.new_sort_field = '';
      vm.new_sort_value = '';
      vm.new_sort_date = '';
      vm.edit_sort_mode = false;
    },
    removeSort: function (sort, e) {
      e.preventDefault();
      var vm = this;
      vm.view_sorts.splice(vm.view_sorts.indexOf(sort), 1);
    },
    cleanLoadTemplates: function(e) {
      e.preventDefault();
      var vm = this;
      vm.$http.post(base_path+'admin/api/templates', {'rescan': true}).then(function (response) {
        if (response.body.length == 0) {
          layer.alert('init error！', {icon: 5});
        } else {
          vm.templates = response.body;
          layer.msg('Clean Success!', {time: 1000, icon: 6});
        }
      }, function (response) {
        layer.alert('init error！', {icon: 5});
      });
  	},
    previewResult: function(e) {
      e.preventDefault();
      var vm = this;
      vm.saveView('temp', e);
    },
    getPreviewResult: function() {
      var vm = this;
      vm.$http.post(base_path+'admin/api/query-result', {
        'view_machine_name': vm.view_machine_name
      }).then(function (response) {
        if (response.body.length == false) {
          layer.alert('preview error！', {icon: 5});
        } else {
          vm.preview_result = response.body;
        }
      }, function (response) {
        layer.alert('preview error！', {icon: 5});
      });
    },
    saveView: function(type, e) {
      e.preventDefault();
      var vm = this;
      if(vm.need_permission){
        vm.view_permissions = $(".dropdown-permission-list select").val();
      }
      vm.$http.post(base_path+'admin/api/save-view', {
        'view_name': vm.view_name,
        'view_machine_name': vm.view_machine_name,
        'view_description': vm.view_description,
        'view_table': vm.view_table,
        'view_relation_table': vm.view_relation_table,
        'view_fields': vm.view_fields,
        'view_filters': vm.view_filters,
        'view_sorts': vm.view_sorts,
        'has_pager': vm.has_pager,
        'view_pager': vm.view_pager,
        'view_template': vm.view_template,
        'template_content': vm.template_content,
        'overwrit_template': vm.overwrit_template,
        'type': type,
        'json_export': vm.json_export,
        'view_path': vm.view_path,
        'view_permissions': vm.view_permissions
      }).then(function (response) {
          if (response.body.length == false) {
            layer.alert('init error！', {icon: 5});
          } else {
            if(response.body == true) {
              layer.msg('Save Success!', {time: 1000, icon: 6});
            }else {
              vm.temp_template = response.body;
              vm.getPreviewResult();
            }
          }
      }, function (response) {
          layer.alert('init error！', {icon: 5});
      });
  	},
    updateField: function() {
      var vm = this;
      vm.fields = {};
      for(var n in vm.tables[vm.view_table].fields){
        vm.fields[vm.view_table+'.'+n] = vm.tables[vm.view_table].fields[n];
      }

      if(vm.tables[vm.view_table].relationship){
        for (var i=0; i<vm.tables[vm.view_table].relationship.length; i++){
          vm.relationships.push(vm.tables[vm.view_table].relationship[i].left.table);
        }
      }
  	},
    updateFilterOp: function() {
      var vm = this;
      var select_table = vm.new_filter_field.split(".")[0];
      var select_filed = vm.new_filter_field.split(".")[1];
      vm.select_field_filter_type = vm.tables[select_table].fields[select_filed].filter_type;
      vm.filter_ops = vm.filters_list[vm.select_field_filter_type];
    },
    updateSortType: function() {
      var vm = this;
      var select_table = vm.new_sort_field.split(".")[0];
      var select_filed = vm.new_sort_field.split(".")[1];
      if(vm.tables[select_table].fields[select_filed].sorttype){
        vm.select_field_sort_type = vm.tables[select_table].fields[select_filed].sorttype;
      }else {
        vm.select_field_sort_type = '';
      }
    },
    addRelationshipFields: function() {
      var vm = this;
      for(var n in vm.tables[vm.view_relation_table].fields){
        vm.fields[vm.view_relation_table+'.'+n] = vm.tables[vm.view_relation_table].fields[n];
      }
  	},
    editThisTemplate: function(e) {
      e.preventDefault();
      var vm = this;
      vm.$http.post(base_path+'admin/api/get-template-content', {'file_name': vm.view_template}).then(function (response) {
          if (response.body.length == 0) {
            layer.alert('init error！', {icon: 5});
          } else {
            vm.template_content = response.body;
          }
      }, function (response) {
          layer.alert('init error！', {icon: 5});
      });
  	},
    scanPermission: function() {
      var vm = this;
      if(vm.need_permission){
        vm.$http.post(base_path+'admin/api/permissions').then(function (response) {
          if (response.body.length != 0) {
            vm.permissions = response.body;
            $('.dropdown-permission-list').dropdown({
              data: response.body,
              searchable: false,
            });
          }
        }, function (response) {
          layer.alert('init error！', {icon: 5});
        });
      }
  	},
    setTextarea: function(type, e) {
      e.preventDefault();
      var vm = this;
      var htmltext = '';
      switch(type)
      {
      case 'htmllist':
        htmltext += '<div class="item-list">\n <ul>\n';
        htmltext += '  @foreach($viewdata as $item)\n  <li>\n';
        if(vm.view_fields != []){
          for (var i=0; i<vm.view_fields.length; i++){
            htmltext += '   <p>{{ $item->'+vm.view_fields[i].split(".")[1]+' }}</p>\n';
          }
        }
        htmltext += '  </li>\n  @endforeach\n';
        htmltext += ' </ul>\n</div>\n';
        break;
      case 'table':
        htmltext += '<table class="layui-table">\n <thead>\n  <tr>\n';
        if(vm.view_fields != []){
          for (var i=0; i<vm.view_fields.length; i++){
            htmltext += '   <th>'+vm.fields[vm.view_fields[i]].name+'</th>\n';
          }
        }
        htmltext += '  </tr>\n </thead>\n <tbody>\n';
        htmltext += '  @foreach($viewdata as $item)\n  <tr>\n';
        if(vm.view_fields != []){
          for (var i=0; i<vm.view_fields.length; i++){
            htmltext += '   <td>{{ $item->'+vm.view_fields[i].split(".")[1]+' }}</td>\n';
          }
        }
        htmltext += '  </tr>\n  @endforeach\n';
        htmltext += ' </tbody>\n</table>\n';
        break;
      case 'html5':
        htmltext += '<!DOCTYPE html>\n<html lang="en">\n<head>\n';
        htmltext += '  <meta charset="UTF-8">\n  <title>'+vm.view_name+'</title>\n';
        htmltext += '</head>\n<body>\n';
        if(vm.view_fields != []){
          htmltext += '  @foreach($viewdata as $item)\n';
          for (var i=0; i<vm.view_fields.length; i++){
            htmltext += '   {{ $item->'+vm.view_fields[i].split(".")[1]+' }}\n';
          }
          htmltext += '  @endforeach\n';
        }
        htmltext += '</body>\n</html>\n';
        break;
      default:
        if(vm.view_fields != []){
          htmltext += '@foreach($viewdata as $item)\n';
          for (var i=0; i<vm.view_fields.length; i++){
            htmltext += '{{ $item->'+vm.view_fields[i].split(".")[1]+' }}\n';
          }
          htmltext += '@endforeach\n\n';
        }
      }
      if(vm.has_pager && (vm.view_pager.type == 'mini' || vm.view_pager.type == 'full')){
        htmltext += '{!! hunter_pager($pager) !!}\n';
      }
      vm.template_content = htmltext;
    },
    setCookie: function (name,value) {
        var exp = new Date();
        exp.setTime(exp.getTime() + 1*60*60*1000);
        document.cookie = name + "="+ escape (value) + ";expires=" + exp.toGMTString();
    },
    getCookie: function (name) {
        var arr,reg=new RegExp("(^| )"+name+"=([^;]*)(;|$)");
        if(arr=document.cookie.match(reg)) return unescape(arr[2]);
        else return null;
    },
  }
})
