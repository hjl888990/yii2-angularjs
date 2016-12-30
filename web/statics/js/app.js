'use strict';

//验证默认密码是否八个8

// Declare app level module which depends on filters, and services
var app = angular.module('app', [
    'ngAnimate',
    'ngCookies',
    'ngStorage',
    'ui.router',
    'ui.bootstrap',
    'ui.load',
    'ui.jq',
    'uiSwitch',
    'ui.validate',
    'pascalprecht.translate',
    'angularFileUpload',
    'ui.bootstrap.datetimepicker',
    'app.filters',
    'app.services',
    'app.directives',
    'app.controllers',
    'app.controllers.loginController',
    'app.controllers.headerController',
])
        .run(function($rootScope, $state, $stateParams, $http) {

            $rootScope.$state = $state;
            $rootScope.$stateParams = $stateParams;
        })





        //国际化配置
        .config(function($translateProvider) {

            // Register a loader for the static files
            // So, the module will search missing translation tables under the specified urls.
            // Those urls are [prefix][langKey][suffix].多语言存储位置
            $translateProvider.useStaticFilesLoader({
                prefix: 'statics/l10n/',
                suffix: '.json'
            });

            // Tell the module what language to use by default默认系统语言
            $translateProvider.preferredLanguage('cn');

            // Tell the module to store the language in the local storage
            $translateProvider.useLocalStorage();

        })

        .config(function($httpProvider) {
            // 注册拦截器-service.js里定义myInterceptor
            $httpProvider.interceptors.push('myInterceptor');
        })
        /**
         * jQuery plugin config use ui-jq directive , config the js and css files that required
         * key: function name of the jQuery plugin
         * value: array of the css js file located
         */
        .constant('JQ_CONFIG', {
            easyPieChart: ['statics/js/jquery/charts/easypiechart/jquery.easy-pie-chart.js'],
            sparkline: ['statics/js/jquery/charts/sparkline/jquery.sparkline.min.js'],
            plot: ['statics/js/jquery/charts/flot/jquery.flot.min.js',
                'statics/js/jquery/charts/flot/jquery.flot.resize.js',
                'statics/js/jquery/charts/flot/jquery.flot.tooltip.min.js',
                'statics/js/jquery/charts/flot/jquery.flot.spline.js',
                'statics/js/jquery/charts/flot/jquery.flot.orderBars.js',
                'statics/js/jquery/charts/flot/jquery.flot.pie.min.js'
            ],
            slimScroll: ['statics/js/jquery/slimscroll/jquery.slimscroll.min.js'],
            sortable: ['statics/js/jquery/sortable/jquery.sortable.js'],
            nestable: ['statics/js/jquery/nestable/jquery.nestable.js',
                'statics/js/jquery/nestable/nestable.css'
            ],
            filestyle: ['statics/js/jquery/file/bootstrap-filestyle.min.js'],
            slider: ['statics/js/jquery/slider/bootstrap-slider.js',
                'statics/js/jquery/slider/slider.css'
            ],
            chosen: ['statics/js/jquery/chosen/chosen.jquery.min.js',
                'statics/js/jquery/chosen/chosen.css'
            ],
            TouchSpin: ['statics/js/jquery/spinner/jquery.bootstrap-touchspin.min.js',
                'statics/js/jquery/spinner/jquery.bootstrap-touchspin.css'
            ],
            wysiwyg: ['statics/js/jquery/wysiwyg/bootstrap-wysiwyg.js',
                'statics/js/jquery/wysiwyg/jquery.hotkeys.js'
            ],
            dataTable: ['statics/js/jquery/datatables/jquery.dataTables.min.js',
                'statics/js/jquery/datatables/dataTables.bootstrap.js',
                'statics/js/jquery/datatables/dataTables.bootstrap.css'
            ],
            vectorMap: ['statics/js/jquery/jvectormap/jquery-jvectormap.min.js',
                'statics/js/jquery/jvectormap/jquery-jvectormap-world-mill-en.js',
                'statics/js/jquery/jvectormap/jquery-jvectormap-us-aea-en.js',
                'statics/js/jquery/jvectormap/jquery-jvectormap.css'
            ],
            footable: ['statics/js/jquery/footable/footable.all.min.js',
                'statics/js/jquery/footable/footable.core.css'
            ]
        })


        .constant('MODULE_CONFIG', {
            select2: ['statics/js/jquery/select2/select2.css',
                'statics/js/jquery/select2/select2-bootstrap.css',
                'statics/js/jquery/select2/select2.min.js',
                'statics/js/modules/ui-select2.js'
            ]
        })

//echarts
        .constant('ECHARTS_CONFIG', {
            echarts: ['statics/js/echarts-2.1.10/echarts.js'],
            paths: ['statics/js/echarts-2.1.10']
        })


        ;
