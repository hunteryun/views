layui.use(["element", "layer"],
function() {
    var $ = layui.jquery,
    element = layui.element,
    layer = layui.layer;
    $(window).on("resize",
    function() {
        var e = $("#tabContainers");
        e.height($(this).height() - 200),
        e.find("iframe").each(function() {
            $(this).height(e.height())
        })
    }).resize();

    // 添加TAB选项卡
    window.addTab = function (elem, tit, url) {
        var card = 'card';                                              // 选项卡对象
        var title = tit ? tit : elem.children('a').html();              // 导航栏text
        var src = url ? url : elem.children('a')[0].dataset.url;           // 导航栏跳转URL
        var id = new Date().getTime();                                  // ID
        // 大于0就是有该选项卡了
        if (src) {
            //新增
            element.tabAdd(card, {
                title: '<span>' + title + '</span>'
                , content: '<iframe src="' + src + '" frameborder="0"></iframe>'
                , id: id
            });
            // 关闭弹窗
            layer.closeAll();
        }
        // 切换相应的ID tab
        element.tabChange(card, id);
        // 提示信息
        // layer.msg(title);
    };

    // 监听顶部左侧导航
    element.on('nav(side-top-left)', function (elem) {
        // 添加tab方法
        window.addTab(elem);
    });

    // 监听顶部右侧导航
    element.on('nav(side-top-right)', function (elem) {
        // 修改skin
        if ($(this).attr('data-skin')) {
            localStorage.skin = $(this).attr('data-skin');
            skin();
        } else {
            // 添加tab方法
            window.addTab(elem);
        }
    });

    // 监听导航(side-main)点击切换页面
    element.on('nav(side-main)', function (elem) {
        // 添加tab方法
        window.addTab(elem);
    });
});
