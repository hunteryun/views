/**
 * main js
 */

 layui.use(['form', 'upload', 'laydate'], function(){
   var form = layui.form
   ,$ = layui.$
   ,layer = layui.layer;

  form.on('select(view_table)', function(data){
     if(data.elem.name == 'view_table'){
       App.view_table = data.value;
       App.updateField();
     }
   });

   form.on('checkbox(has_description)', function(data){
     App.has_description = true;
   });

   form.render();

   $('#upload-image-button').on('click', function(){
     layer.open({
       type: 1,
       title: 'Upload Image',
       closeBtn: 1,
       shadeClose: true,
       area: ['420px', '240px'],
       content: $('#pop-upload-image')
     });
   });

   //监听提交
   form.on('submit(saveGallerySubmit)', function(data) {
       $.ajax({
           type: "POST",
           url: "/customer/save-gallery", //后台程序地址
           data: data.field, //需要post的数据
           success: function(msg) {
               if (!msg) {
                 layer.msg('Add failed!', {
                     icon: 5
                 });
               } else {
                 layer.closeAll();
                 layer.msg('Add success!', {
                     icon: 1,
                     time: 2000,
                     shade: 0.1
                 });
               }
           }
       });
       return false;
   });
 });

 function imageSet(pid) {
     layui.use(['layer'], function() {
         var $ = layui.jquery;
         var layer = layui.layer;

         $.ajax({
             type: "GET",
             url: '/image/set/' + pid,
             success: function(msg) {
                 if (msg) { //如果成功了
                     location.reload();
                 } else {
                     layer.msg('Set image failed!', {
                         icon: 5
                     });
                 }
             }
         });
     });
 }
