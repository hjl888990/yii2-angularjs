// 加载js
(function (argument) {
    var jsList = {
        // 公共js列表
        common: [
             '/js/jquery/lodash.js',
            '/js/jquery/jquery.min.js',
            '/js/jquery/slimscroll/jquery.slimscroll.min.js',
            '/js/jquery/easydialog.js',
            '/js/jquery/imgShowBtS.js',

            '/js/angular/angular.min.js',
            '/js/angular/angular-cookies.min.js',
            '/js/angular/angular-animate.min.js',
            '/js/angular/angular-file-upload.js',
            '/js/angular/angular-ui-router.min.js',
            '/js/angular/angular-translate.js',
            '/js/angular/ngStorage.min.js',
            '/js/angular/ui-load.js',
            '/js/angular/ui-jq.js',
            '/js/angular/ui-validate.js',
            '/js/angular/ui-bootstrap-tpls.min.js',
            '/js/angular/angular-ui-switch.min.js',
            '/js/angular/datetime-picker.js',
           // '/js/angular/isteven-multi-select.js',
            '/js/ueditor/ueditor.config.js',
            '/js/ueditor/angular-ueditor.js',

            '/js/app.js',
            '/js/appRoute.js',
            '/js/services.js',
            '/js/filters.js',
            '/js/directives.js',
            '/js/controllers/controllers.js',
            
            '/js/controllers/loginController.js',
            '/js/controllers/headerController.js',
        ]
    };

    //处理加载公共js
    $.each(jsList.common, function (k, v) {
        //$('body').append('<script src="'+ baseConfig.commonDir + addVersion(this) +'"></script>');
        jsList.common[k] = baseConfig.commonDir + addVersion(this);
    });

    //load and block 异步加载，顺序执行
    $LAB
        .setOptions({
            'AlwaysPreserveOrder': true
        })
        .script(jsList.common)
        .wait(function() {
            //bootstrap angular
            angular.bootstrap(document, ['app']);
        })
    ;
})();
