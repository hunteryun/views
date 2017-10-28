/**
 * vue app
 */
Vue.http.options.emulateJSON = true;

var App = new Vue({
  el: '#App',
  data: {
    view_name: 'default',
    view_description: '',
    view_table: '',
    view_fields: [],
    view_template: '',
    view_relation_table: '',
    has_description: false,
    tables: [],
    templates: [],
    template_content: '',
    fields: {},
    preview_result: '',
    create_new_template: 'new',
    overwrit_template: true,
    show_editor: true,
    temp_template: '',
    relationships: []
  },
  mounted: function () {
    this.initTablesList();
    this.initTemplatesList();
  },
  methods: {
    initTablesList: function() {
      var vm = this;
      vm.$http.get('/admin/api/tables').then(function (response) {
          if (response.body.length == 0) {
            layer.alert('init error！', {icon: 5});
          } else {
            vm.tables = response.body;
          }
      }, function (response) {
          layer.alert('init error！', {icon: 5});
      });
  	},
    initTemplatesList: function() {
      var vm = this;
      vm.$http.post('/admin/api/templates').then(function (response) {
          if (response.body.length == 0) {
            layer.alert('init error！', {icon: 5});
          } else {
            vm.templates = response.body;
          }
      }, function (response) {
          layer.alert('init error！', {icon: 5});
      });
  	},
    cleanLoadTemplates: function(e) {
      e.preventDefault();
      var vm = this;
      vm.$http.post('/admin/api/templates', {'rescan': true}).then(function (response) {
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

      vm.$nextTick(function () {
        vm.$http.post('/admin/api/query-result', {
          'view_name': vm.view_name
        }).then(function (response) {
            if (response.body.length == false) {
              layer.alert('preview error！', {icon: 5});
            } else {
              vm.preview_result = response.body;
            }
        }, function (response) {
            layer.alert('preview error！', {icon: 5});
        });
      })
  	},
    saveView: function(type, e) {
      e.preventDefault();
      var vm = this;
      vm.$http.post('/admin/api/save-view', {
        'view_name': vm.view_name,
        'view_description': vm.view_description,
        'view_table': vm.view_table,
        'view_relation_table': vm.view_relation_table,
        'view_fields': vm.view_fields,
        'view_template': vm.view_template,
        'template_content': vm.template_content,
        'overwrit_template': vm.overwrit_template,
        'type': type
      }).then(function (response) {
          if (response.body.length == false) {
            layer.alert('init error！', {icon: 5});
          } else {
            if(response.body == true) {
              layer.msg('Save Success!', {time: 1000, icon: 6});
            }else {
              vm.temp_template = response.body;
            }
          }
      }, function (response) {
          layer.alert('init error！', {icon: 5});
      });
  	},
    updateField: function() {
      var vm = this;
      for(var n in vm.tables[vm.view_table].fields){
        vm.fields[vm.view_table+'.'+n] = vm.tables[vm.view_table].fields[n];
      }

      if(vm.tables[vm.view_table].relationship.length != 0){
        for (var i=0; i<vm.tables[vm.view_table].relationship.length; i++){
          vm.relationships.push(vm.tables[vm.view_table].relationship[i].left.table);
        }
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
      vm.$http.post('/admin/api/get-template-content', {'file_name': vm.view_template}).then(function (response) {
          if (response.body.length == 0) {
            layer.alert('init error！', {icon: 5});
          } else {
            vm.template_content = response.body;
          }
      }, function (response) {
          layer.alert('init error！', {icon: 5});
      });
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
        vm.template_content = htmltext;
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
        htmltext += ' </tbody>\n</table>';
        vm.template_content = htmltext;
        break;
      default:
        if(vm.view_fields != []){
          htmltext += '@foreach($viewdata as $item)\n';
          for (var i=0; i<vm.view_fields.length; i++){
            htmltext += '{{ $item->'+vm.view_fields[i].split(".")[1]+' }}\n';
          }
          htmltext += '@endforeach\n\n';
        }
        vm.template_content = htmltext;
      }
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
