'use strict';
/* Directives */
// All the directives rely on jQuery.
angular.module('app.directives', ['ui.load'])
    .directive('hello', function () {
        return {
            restrict: 'AE',
            //template:'<div>hello world</div>',
            templateUri: 'statics/tpl/knowledge/knowledgeCategoryUi.html',
            transclude: true,
            replace: true,
        }
    })
    
    // 时间控件
    .directive('date', function () {
        return {
            restrict: 'AE',
            scope: {
                ngModel: '=',
                format: '@',
                enableTime: '=',
                enableDate: '=',
                minDate: '=',
                maxDate: '=',
                datepickerMode: '=',
                closeInput: '=',
                readonly: '=',
                ngReadonly: '='
            },
            template: '<div class="input-group"><input type="text" class="form-control" datepicker-mode="datepickerMode" datetime-picker="{{format}}" ng-model="ngModel" is-open="isOpen" ng-readonly="ngReadonly || readonly" enable-time="enableTime" enable-date="enableDate" min-date="minDate" max-date="maxDate" ng-required="showRequired" ng-click="!closeInput && openCalendar($event, prop)" /><span class="input-group-btn"><button type="button" class="btn btn-default" ng-click="openCalendar($event, prop)"><i class="fa fa-calendar"></i></button></span></div>',
            link: function (scope, el, attr) {
                var _scope = scope;
                if (attr.required) {
                    scope.showRequired = true;
                }

                scope.format = scope.format || 'yyyy-MM-dd';
                scope.isOpen = false;
                scope.openCalendar = function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    _scope.isOpen = true;
                };
            }
        };
    })

    .directive('uiModule', ['MODULE_CONFIG', 'uiLoad', '$compile', function (MODULE_CONFIG, uiLoad, $compile) {
        return {
            restrict: 'A',
            compile: function (el, attrs) {
                var contents = el.contents().clone();
                return function (scope, el, attrs) {
                    el.contents().remove();
                    uiLoad.load(MODULE_CONFIG[attrs.uiModule])
                        .then(function () {
                            $compile(contents)(scope, function (clonedElement, scope) {
                                el.append(clonedElement);
                            });
                        });
                }
            }
        };
    }])

    .directive('uiShift', ['$timeout', function ($timeout) {
        return {
            restrict: 'A',
            link: function (scope, el, attr) {
                // get the $prev or $parent of this el
                var _el = $(el),
                    _window = $(window),
                    prev = _el.prev(),
                    parent,
                    width = _window.width();

                !prev.length && (parent = _el.parent());

                function sm() {
                    $timeout(function () {
                        var method = attr.uiShift;
                        var target = attr.target;
                        _el.hasClass('in') || _el[method](target).addClass('in');
                    });
                }

                function md() {
                    parent && parent['prepend'](el);
                    !parent && _el['insertAfter'](prev);
                    _el.removeClass('in');
                }

                (width < 768 && sm()) || md();

                _window.resize(function () {
                    if (width !== _window.width()) {
                        $timeout(function () {
                            (_window.width() < 768 && sm()) || md();
                            width = _window.width();
                        });
                    }
                });
            }
        };
    }])

    .directive('uiToggleClass', ['$timeout', '$document', function ($timeout, $document) {
        return {
            restrict: 'AC',
            link: function (scope, el, attr) {
                el.on('click', function (e) {
                    e.preventDefault();
                    var classes = attr.uiToggleClass.split(','),
                        targets = (attr.target && attr.target.split(',')) || Array(el),
                        key = 0;
                    angular.forEach(classes, function (_class) {
                        var target = targets[(targets.length && key)];
                        (_class.indexOf('*') !== -1) && magic(_class, target);
                        $(target).toggleClass(_class);
                        key++;
                    });
                    $(el).toggleClass('active');

                    function magic(_class, target) {
                        var patt = new RegExp('\\s' +
                            _class.replace(/\*/g, '[A-Za-z0-9-_]+').split(' ').join('\\s|\\s') +
                            '\\s', 'g');
                        var cn = ' ' + $(target)[0].className + ' ';
                        while (patt.test(cn)) {
                            cn = cn.replace(patt, ' ');
                        }
                        $(target)[0].className = $.trim(cn);
                    }
                });
            }
        };
    }])

    .directive('uiNav', ['$timeout', function ($timeout) {
        return {
            restrict: 'AC',
            link: function (scope, el, attr) {
                var _window = $(window),
                    _mb = 768,
                    wrap = $('.app-aside'),
                    next,
                    backdrop = '.dropdown-backdrop';
                // unfolded
                el.on('click', 'a', function (e) {
                    next && next.trigger('mouseleave.nav');
                    var _this = $(this);
                    _this.parent().siblings(".active").toggleClass('active');
                    _this.next().is('ul') && _this.parent().toggleClass('active') && e.preventDefault();
                    // mobile
                    _this.next().is('ul') || ((_window.width() < _mb) && $('.app-aside').removeClass('show off-screen'));
                });

                // folded & fixed        
                el.on('mouseenter', 'a', function (e) {
                    next && next.trigger('mouseleave.nav');
                    $('> .nav', wrap).remove();
                    if (!$('.app-aside-fixed.app-aside-folded').length || (_window.width() < _mb)) return;
                    var _this = $(e.target),
                        top, w_h = $(window).height(),
                        offset = 50,
                        min = 150;

                    !_this.is('a') && (_this = _this.closest('a'));
                    if (_this.next().is('ul')) {
                        next = _this.next();
                    } else {
                        return;
                    }

                    _this.parent().addClass('active');
                    top = _this.parent().position().top + offset;
                    next.css('top', top);
                    if (top + next.height() > w_h) {
                        next.css('bottom', 0);
                    }
                    if (top + min > w_h) {
                        next.css('bottom', w_h - top - offset).css('top', 'auto');
                    }
                    next.appendTo(wrap);

                    next.on('mouseleave.nav', function (e) {
                        $(backdrop).remove();
                        next.appendTo(_this.parent());
                        next.off('mouseleave.nav').css('top', 'auto').css('bottom', 'auto');
                        _this.parent().removeClass('active');
                    });

                    $('.smart').length && $('<div class="dropdown-backdrop"/>').insertAfter('.app-aside').on('click', function (next) {
                        next && next.trigger('mouseleave.nav');
                    });

                });

                wrap.on('mouseleave', function (e) {
                    next && next.trigger('mouseleave.nav');
                    $('> .nav', wrap).remove();
                });
            }
        };
    }])

    .directive('uiScroll', ['$location', '$anchorScroll', function ($location, $anchorScroll) {
        return {
            restrict: 'AC',
            link: function (scope, el, attr) {
                el.on('click', function (e) {
                    $location.hash(attr.uiScroll);
                    $anchorScroll();
                });
            }
        };
    }])

    .directive('uiFullscreen', ['uiLoad', function (uiLoad) {
        return {
            restrict: 'AC',
            template: '<i class="fa fa-expand fa-fw text"></i><i class="fa fa-compress fa-fw text-active"></i>',
            link: function (scope, el, attr) {
                el.addClass('hide');
                uiLoad.load('statics/js/libs/screenfull.min.js').then(function () {
                    if (screenfull.enabled) {
                        el.removeClass('hide');
                    }
                    el.on('click', function () {
                        var target;
                        attr.target && (target = $(attr.target)[0]);
                        el.toggleClass('active');
                        screenfull.toggle(target);
                    });
                });
            }
        };
    }])

    .directive('uiButterbar', ['$rootScope', '$anchorScroll', function ($rootScope, $anchorScroll) {
        return {
            restrict: 'AC',
            template: '<span class="bar"></span>',
            link: function (scope, el, attrs) {
                el.addClass('butterbar hide');
                scope.$on('$stateChangeStart', function (event) {
                    $anchorScroll();
                    el.removeClass('hide').addClass('active');
                });
                scope.$on('$stateChangeSuccess', function (event, toState, toParams, fromState) {
                    event.targetScope.$watch('$viewContentLoaded', function () {
                        el.addClass('hide').removeClass('active');
                    })
                });
            }
        };
    }])

    .directive('setNgAnimate', ['$animate', function ($animate) {
        return {
            link: function ($scope, $element, $attrs) {
                $scope.$watch(function () {
                    return $scope.$eval($attrs.setNgAnimate, $scope);
                }, function (valnew, valold) {
                    $animate.enabled(!!valnew, $element);
                });
            }
        };
    }])

    //echarts directives
    .directive('echarts', function (uiLoad, ECHARTS_CONFIG) {
        return {
            restrict: 'AE',
            template: '',
            scope: {
                echartsOptions: '=',
                echartsCallback: '&'
            },
            link: function (scope, el, attr) {
                function setOptions() {
                    var echartsStr, echartsCallback,
                        tempArr = [],
                        echartsEvent = '',
                        moduleJsArr = ['echarts'],
                        options = scope.echartsOptions;

                    if (!options) {
                        console.log('兄弟，你是不是没有传入echarts的options配置参数啊');
                        return false;
                    }

                    echartsEvent = attr['echartsEvent'];
                    echartsCallback = scope.echartsCallback;
                    echartsStr = attr['echarts'];
                    if (echartsStr) {
                        tempArr = scope.$eval(attr['echarts']);
                    }

                    if (tempArr.length) {
                        moduleJsArr = moduleJsArr.concat(tempArr);
                    }

                    // 转换成对象
                    // options = scope.$eval(options);

                    // 先载完文件才执行
                    uiLoad.load(ECHARTS_CONFIG.echarts).then(function () {
                        // 路径配置
                        require.config({
                            paths: {
                                echarts: ECHARTS_CONFIG.paths
                            }
                        });
                        // 使用
                        require(
                            // 按需加载模块
                            moduleJsArr,
                            function (ec) {
                                // 基于准备好的dom，初始化echarts图表
                                var myChart = ec.init(angular.element(el)[0]);
                                myChart.showLoading({
                                    text: "图表数据正在努力加载..."
                                });
                                myChart.hideLoading();

                                var option = options;

                                // 为echarts对象加载数据 
                                myChart.setOption(option);
                                if (echartsEvent) {
                                    myChart.on(echartsEvent, function (config) {
                                        /*注： 页面方法调用的参数也要叫config,这个要一致*/
                                        typeof echartsCallback == 'function' && echartsCallback({
                                            config: config
                                        });
                                    })
                                }
                                // window.onresize = myChart.resize;
                                window.addEventListener('resize', function () {
                                    myChart.resize();
                                }, false);
                            }
                        );
                    });

                }

                // setOptions();
                // update when charts config changes
                scope.$watch(function () {
                    return scope.echartsOptions
                }, function (value) {
                    if (value) {
                        setOptions();
                    }
                });

            }
        };
    })

    .directive('googleMap', function () {
        // directive link function
        var link = function (scope, element, attrs) {
            var map, infoWindow;
            var markers = [];

            // map config
            var mapOptions = {
                center: new google.maps.LatLng(50, 2),
                zoom: 8,
                mapTypeId: google.maps.MapTypeId.ROADMAP,
                scrollwheel: false
            };

            // init the map
            function initMap() {
                if (map === void 0) {
                    map = new google.maps.Map(element[0], mapOptions);
                }
            }

            // place a marker
            function setMarker(map, position, title, content) {
                var marker;
                var markerOptions = {
                    position: position,
                    map: map,
                    title: title,
                    icon: 'https://maps.google.com/mapfiles/ms/icons/green-dot.png'
                };

                marker = new google.maps.Marker(markerOptions);
                markers.push(marker); // add marker to array

                google.maps.event.addListener(marker, 'click', function () {
                    // close window if not undefined
                    if (infoWindow !== void 0) {
                        infoWindow.close();
                    }
                    // create new window
                    var infoWindowOptions = {
                        content: content
                    };
                    infoWindow = new google.maps.InfoWindow(infoWindowOptions);
                    infoWindow.open(map, marker);
                });
            }

            // show the map and place some markers
            initMap();

            setMarker(map, new google.maps.LatLng(51.508515, -0.125487), 'London', 'Just some content');
            setMarker(map, new google.maps.LatLng(52.370216, 4.895168), 'Amsterdam', 'More content');
            setMarker(map, new google.maps.LatLng(48.856614, 2.352222), 'Paris', 'Text here');
        };

        return {
            restrict: 'A',
            template: '<div id="gmaps"></div>',
            replace: true,
            link: link
        };
    })
