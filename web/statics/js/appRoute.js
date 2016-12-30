'use strict';
//路由配置
app.config(function($stateProvider, $urlRouterProvider, $controllerProvider, $compileProvider, $filterProvider, $provide) {

    // lazy controller, directive and service
    app.controller = $controllerProvider.register;
    app.directive = $compileProvider.directive;
    app.filter = $filterProvider.register;
    app.factory = $provide.factory;
    app.service = $provide.service;
    app.constant = $provide.constant;
    app.value = $provide.value;



    //模板将被插入哪里?状态被激活时，它的模板会自动插入到父状态对应的模板中包含ui-view属性的元素内部。如果是顶层的状态，那么它的父模板就是index.html。

    $urlRouterProvider
            .otherwise('/app/user');//规则之外的跳转

    $stateProvider

            .state('app', {
                abstract: true,
                url: '/app',
                templateUrl: 'statics/tpl/app.html'
            })

            //------------------------------------------------------模块级路由设置------------------------------------------------------
            //知识库模块
            .state('app.knowledge', {
                url: '/knowledge', //如果你使用绝对 url 匹配的方式，那么你需要给你的url字符串加上特殊符号"^"
                template: '<div ui-view class="fade-in-up"></div>'
            })
            //登录页模块
            .state('access', {
                url: '/access',
                template: '<div ui-view class="fade-in-right-big smooth"></div>'
            })
            //用户模块
            .state('app.user', {
                url: '/user',
                template: '<div ui-view class="fade-in-up"></div>'
            })

            //------------------------------------------------------页面级路由展示------------------------------------------------------
            .state('access.signin', {
                url: '/signin',
                templateUrl: 'statics/tpl/user/signin.html'
            })
            .state('access.signup', {
                url: '/signup',
                templateUrl: 'statics/tpl/user/signin.html'
            })
            
           //------------------------------------------------------用户管理------------------------------------------------------
            .state('app.user.userList', {
                url: '/userList',
                templateUrl: 'statics/tpl/user/userList.html',
                resolve: {
                    deps: ['uiLoad',
                        function(uiLoad) {
                            return uiLoad.load(['statics/js/controllers/userController.js']);
                        }]
                }
            })
            
            //------------------------------------------------------知识库管理------------------------------------------------------
            //知识库分类
            .state('app.knowledge.knowledgeCategoryList', {
                url: '/knowledgeCategoryList',
                templateUrl: 'statics/tpl/knowledge/storeKnowledgeCat_list.html',
                resolve: {
                    deps: ['uiLoad',
                        function(uiLoad) {
                            return uiLoad.load([
                                'statics/js/controllers/knowledge/knowledgeCatController.js'
                            ]);
                        }
                    ]
                }
            })

}
);
