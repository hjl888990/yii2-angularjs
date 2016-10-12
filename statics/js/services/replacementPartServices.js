'use strict';
/* 公共服务 Services */
// Demonstrate how to register services
angular.module('app.replacementPartServices', [])

//领料申请服务
        .factory('replacementPartPickServices', function(dialog) {
            return {
                show: function(callBack, id) {
                    var instance = dialog.open({
                        size: 'lg',
                        templateUrl: 'statics/tpl/replacement/replacementPartPickTemplate.html',
                        controller: function($scope, $http, $timeout, tipDialog) {
                            var msg_sccg = 'Successfully';

                            $scope.forms = {warehouseCode: '',warehouseName:'',wareareaCode:'', memo: '', isBorrowSwitch: 0, };
                            $scope.searchQus = {goodsCode: '', goodsName: ''};
                            $scope.selPrice = 0;
                            $scope.warehouseFund = 0;
                            $scope.step = true;
                            $scope.type = 10;
                            $scope.load = function() {
                                $http.get('/index.php?r=ServiceNode/ajaxGetWarehouse').success(function(res) {
                                    if (res.ret == true) {
                                        var result = res.data;
                                        $scope.warehouseList = result.data;
                                        if ($scope.warehouseList.length > 0) {
                                            $scope.forms.warehouseCode = $scope.warehouseList[0]['warehouseCode'];
                                            $scope.forms.warehouseName = $scope.warehouseList[0]['warehouseName'];
                                            var areaList =  $scope.warehouseList[0]['areaList'];
                                            for(var i =0;i<areaList.length;i++){
                                                if(areaList[i]['networkPointType'] == 1){
                                                    $scope.forms.wareareaCode = areaList[i]['areaCode'];
                                                    break;
                                                }
                                            }
                                            $scope.showRPList();
                                        }
                                    } else {
                                        tipDialog.open({title: 'Notice', template: res.errMsg, isOk: true, timeOut: 2000});
                                    }
                                });

                            }
                            
                            $scope.returnApply = function() {
                                $scope.step = true;
                            }
                            
                            //价格查询
                            $scope.cheeckPrice = function() {
                                var g = /^[1-9]*[1-9][0-9]*$/;
                                var price = 0;
                                var isSelect = false;
                                for (var i = 0; i < $scope.RPList.length; i++) {
                                    if ($scope.RPList[i]['isSelect']) {
                                        var aplyNum = $scope.RPList[i]['aplyNum'];
                                        if (!g.test(aplyNum)) {
                                            tipDialog.open({title: 'Notice', template: '申请数量必须是大于0的整数！', isOk: true, timeOut: 2000});
                                            return false;
                                        }
                                        price += $scope.RPList[i]['price'] * $scope.RPList[i]['aplyNum'];
                                        isSelect = true;
                                    }
                                }
                                $scope.selPrice = price;
                                if (!isSelect){
                                    tipDialog.open({title: 'Notice', template: '至少勾选一项！', isOk: true, timeOut: 2000});
                                    return false;
                                }
                                
                                if (price > $scope.warehouseFund) {
                                    tipDialog.open({title: 'Notice', template: '申请总额超出可用保证金总额！', isOk: true, timeOut: 2000});
                                    return false;
                                }
                                $scope.step = false;
                            }

                            //提交
                            $scope.submit = function() {
                                var price = 0;
                                var g = /^[1-9]*[1-9][0-9]*$/;
                                for (var i = 0; i < $scope.RPList.length; i++) {
                                    if ($scope.RPList[i]['isSelect']) {
                                        var aplyNum = $scope.RPList[i]['aplyNum'];
                                        if (!g.test(aplyNum)) {
                                            tipDialog.open({title: 'Notice', template: '申请数量必须是大于0的整数！', isOk: true, timeOut: 2000});
                                            return false;
                                        }
                                        price += $scope.RPList[i]['price'] * $scope.RPList[i]['aplyNum'];
                                    }
                                }

                                if (!g.test(price)) {
                                    tipDialog.open({title: 'Notice', template: '申请数量必须是大于0的整数！', isOk: true, timeOut: 2000});
                                    return false;
                                }

                                if (price > $scope.warehouseFund) {
                                    tipDialog.open({title: 'Notice', template: '申请总金额不能大于保证金可用额度！', isOk: true, timeOut: 2000});

                                    return false;
                                }

                                if ($scope.forms.warehouseCode == '' || $scope.forms.warehouseCode == null || typeof ($scope.forms.warehouseCode) == "undefined") {
                                    tipDialog.open({title: 'Notice', template: '仓库编号为空！', isOk: true, timeOut: 2000});
                                    return false;
                                } 
                                if ($scope.forms.warehouseName == '' || $scope.forms.warehouseName == null || typeof ($scope.forms.warehouseName) == "undefined") {
                                    tipDialog.open({title: 'Notice', template: '仓库名称为空！', isOk: true, timeOut: 2000});
                                    return false;
                                } 
                                if ($scope.forms.wareareaCode == '' || $scope.forms.wareareaCode == null || typeof ($scope.forms.wareareaCode) == "undefined") {
                                    tipDialog.open({title: 'Notice', template: '仓库库区编号为空！', isOk: true, timeOut: 2000});
                                    return false;
                                } 
                                

                                var sparePartGoodsList = [];
                                for (var i = 0; i < $scope.RPList.length; i++) {
                                    if ($scope.RPList[i]['isSelect']) {
                                        var sparePartGoods = {
                                            goodsCode: $scope.RPList[i]['goodsCode'],
                                            count: $scope.RPList[i]['aplyNum'],
                                            goodsName: $scope.RPList[i]['goodsName'],
                                            price: $scope.RPList[i]['price'],
                                            currency: $scope.RPList[i]['currency'],
                                            sourceId:''
                                        };
                                        sparePartGoodsList.push(sparePartGoods);
                                    }

                                }

                                if (sparePartGoodsList.length <= 0) {
                                    tipDialog.open({title: 'Notice', template: '请选择申请领料备件商品！', isOk: true, timeOut: 2000});
                                    return false;
                                } else {
                                    $scope.forms.sparePartGoodsList = sparePartGoodsList;
                                }
                                //领料申请
                                $http.post('/index.php?r=ReplacementPart/addPickSparePartsApplySheet', {params: $scope.forms}).success(function(res) {
                                    if (res.ret == true) {
                                        if (typeof callBack == 'function') {
                                            callBack();
                                        }
                                        instance.close();
                                    } else {
                                        tipDialog.open({title: 'Notice', template: res.errMsg, isOk: true, timeOut: 2000})
                                    }
                                });
                            }




                            // 显示备件商品列表
                            $scope.showRPList = function() {
                                if ($scope.forms.warehouseCode == '' || $scope.forms.warehouseCode == null || typeof ($scope.forms.warehouseCode) == "undefined") {
                                    tipDialog.open({title: 'Notice', template: '仓库编号为空！', isOk: true, timeOut: 2000});
                                    return false;
                                }
                                var warehouseList = $scope.warehouseList;
                                for(var i =0;i<warehouseList.length;i++){
                                    if(warehouseList[i]['warehouseCode'] == $scope.forms.warehouseCode){
                                       $scope.forms.warehouseName = warehouseList[i]['warehouseName']; 
                                       var areaList =  warehouseList[i]['areaList'];
                                            for(var j =0;j<areaList.length;j++){
                                                if(areaList[j]['networkPointType'] == 1){
                                                    $scope.forms.wareareaCode = areaList[j]['areaCode'];
                                                    break;
                                                }
                                            } 
                                    }
                                }
                                            
                                //备件价格列表
                                var params = {warehouseCode: $scope.forms.warehouseCode};
                                $http.get('/index.php?r=ReplacementPart/findSparePartsPrice', {params: params}).success(function(res) {
                                    if (res.ret == true) {
                                        var result = res.data;
                                        for (var i = 0; i < result.RPList.length; i++) {
                                            result.RPList[i]['isSelect'] = false;
                                            result.RPList[i]['aplyNum'] = 0;
                                        }
                                        $scope.RPList = result.RPList;

                                        if (result.RPList != '' && result.RPList != null && typeof (result.RPList) != "undefined") {
                                            //仓库保证金详情
                                            $http.get('/index.php?r=ReplacementPart/findDetailWarehouseFund', {params: params}).success(function(res) {
                                                if (res.ret == true) {
                                                    var result = res.data;
                                                    $scope.warehouseFund = result.warehouseFund.canUseAmount;
                                                } else {
                                                    $scope.warehouseFund = 0;
                                                    tipDialog.open({title: 'Notice', template: res.errMsg, isOk: true, timeOut: 2000})
                                                }
                                            });
                                        }

                                    } else {
                                        tipDialog.open({title: 'Notice', template: res.errMsg, isOk: true, timeOut: 2000})
                                    }
                                });

                            };

                            //前段sku,name过滤器
                            $scope.searchRPskuFilter = function(RPList) {
                                var keyword = new RegExp($scope.searchQus.goodsCode, 'i');
                                return !$scope.searchQus.goodsCode || keyword.test(RPList.goodsCode);
                            };
                            $scope.searchRPnameFilter = function(RPList) {
                                var keyword = new RegExp($scope.searchQus.goodsName, 'i');
                                return !$scope.searchQus.goodsName || keyword.test(RPList.goodsName);
                            };

                            //重置查询
                            $scope.resetSearchQuer = function() {
                                $scope.searchQus.goodsCode = '';
                                $scope.searchQus.goodsName = '';
                            };

                            $scope.isBorrowSwitch = function(srcObj) {
                                var $_this = $(srcObj.target);
                                if ($_this.prop('checked')) {
                                    $scope.forms.isBorrowSwitch = 1;
                                } else {
                                    $scope.forms.isBorrowSwitch = 0;
                                }
                            }



                            //选中置顶效果
                            $scope.topTRTAble = function(srcObj, code) {
                                var $_this = $(srcObj.target);
                                var code = $_this.val();
                                if ($_this.prop('checked')) {
                                    var isOver = $scope.selPriceTotal(code);
                                    if (!isOver) {
                                        $_this.prop('checked', false);
                                        return false;
                                    }
                                }

                                if ($_this.prop('checked')) {
                                    $scope.RPList = $scope.stick($scope.RPList, code, 'id', 1);
                                } else {
                                    $scope.RPList = $scope.stick($scope.RPList, code, 'id', 0);
                                    var price = 0;
                                    for (var i = 0; i < $scope.RPList.length; i++) {
                                        if ($scope.RPList[i]['isSelect']) {
                                            price += $scope.RPList[i]['price'] * $scope.RPList[i]['aplyNum'];
                                        }
                                    }
                                    $scope.selPrice = price;
                                }

                            };

                            //选中价格统计
                            $scope.selPriceTotal = function(code) {
                                var g = /^[1-9]*[1-9][0-9]*$/;
                                for (var i = 0; i < $scope.RPList.length; i++) {
                                    if ($scope.RPList[i]['id'] == code) {
                                        var aplyNum = $scope.RPList[i]['aplyNum'];
                                        if (!g.test(aplyNum)) {
                                            tipDialog.open({title: 'Notice', template: '申请数量必须大于0！', isOk: true, timeOut: 2000});
                                            return false;
                                        }
                                        $scope.RPList[i]['isSelect'] = true;
                                    }
                                }

                                var price = 0;
                                for (var i = 0; i < $scope.RPList.length; i++) {
                                    if ($scope.RPList[i]['isSelect']) {
                                        price += $scope.RPList[i]['price'] * $scope.RPList[i]['aplyNum'];
                                    }
                                }
                                if (price > $scope.warehouseFund) {
                                    for (var i = 0; i < $scope.RPList.length; i++) {
                                        if ($scope.RPList[i]['id'] == code) {
                                            $scope.RPList[i]['isSelect'] = false;
                                        }
                                    }

                                    tipDialog.open({title: 'Notice', template: '申请总额超出可用保证金总额！', isOk: true, timeOut: 2000});
                                    return false;
                                }
                                $scope.selPrice = price;
                                return  true;
                            };


                            /**
                             * 
                             * @param {type} ary原数组
                             * @param {type} val选中字段值
                             * @param {type} key选中字段名称
                             * @param {type} type：0置底；1置顶
                             * @returns {unresolved}
                             */
                            $scope.stick = function(ary, val, key, type) {
                                var midAry = new Array();
                                var n = 0;
                                var isSelect = true;
                                if (type == 0) {
                                    isSelect = false;
                                    for (var i = 0; i < ary.length; i++) {
                                        if (ary[i][key] == val) {
                                            ary[i]['isSelect'] = isSelect;
                                            midAry = ary[i];
                                            ary.splice(i, 1);//删除元素
                                            ary.push(midAry);//末尾新增元素
                                            break;
                                        }
                                    }
                                } else {
                                    for (var i = 0; i < ary.length; i++) {
                                        if (ary[i][key] == val) {
                                            ary[i]['isSelect'] = isSelect;
                                            midAry = ary[i];
                                            ary.splice(i, 1);//删除元素
                                            ary.unshift(midAry);//开头新增元素
                                            break;
                                        }
                                    }
                                }
                                return ary;
                            }

                        }
                    });
                }
            };
        })

//退料申请服务
        .factory('replacementPartBackServices', function(dialog) {
            return {
                show: function(callBack, id) {
                    var instance = dialog.open({
                        size: 'lg',
                        templateUrl: 'statics/tpl/replacement/replacementPartBackTemplate.html',
                        controller: function($scope, $http, tipDialog) {

                            var msg_sccg = 'Successfully';

                            $scope.forms = {warehouseCode: '', warehouseName: '', memo: '', isBorrowSwitch: 0, wareareaCode: '', sparePartGoodsList: ''};
                            $scope.searchQus = {goodsCode: '', goodsName: ''};
                            $scope.selPrice = 0;
                            $scope.warehouseFund = 0;
                            $scope.step = true;
                            $scope.type = 20;
                            $scope.currency = '';
                            $scope.load = function() {
                                $http.get('/index.php?r=ServiceNode/ajaxGetWarehouse').success(function(res) {
                                    if (res.ret == true) {
                                        var result = res.data;
                                        $scope.warehouseList = result.data;
                                        if ($scope.warehouseList.length > 0) {
                                            $scope.forms.warehouseCode = $scope.warehouseList[0]['warehouseCode'];
                                            $scope.showRPList(1);
                                        }
                                    } else {
                                        tipDialog.open({title: 'Notice', template: res.errMsg, isOk: true, timeOut: 2000});
                                    }
                                });
                            }

                            // 显示备件商品列表
                            $scope.showRPList = function(type) {
                                if ($scope.forms.warehouseCode == '' || $scope.forms.warehouseCode == null || typeof ($scope.forms.warehouseCode) == "undefined") {
                                    tipDialog.open({title: 'Notice', template: '仓库编号为空！', isOk: true, timeOut: 2000});
                                    return false;
                                }
                                var warehouseList = $scope.warehouseList;
                                for(var i =0;i<warehouseList.length;i++){
                                    if(warehouseList[i]['warehouseCode'] == $scope.forms.warehouseCode){
                                       $scope.forms.warehouseName = warehouseList[i]['warehouseName'];
                                        if (type == 1) {
                                            var areaList = warehouseList[i]['areaList'];
                                            $scope.warehouseareaList = areaList;
                                            if (areaList.length > 0) {
                                                $scope.forms.wareareaCode = areaList[0]['areaCode'];
                                            }
                                        }
                                       
                                    }
                                }
                                if ($scope.forms.warehouseName == '' || $scope.forms.warehouseName == null || typeof ($scope.forms.warehouseName) == "undefined") {
                                    tipDialog.open({title: 'Notice', template: '仓库名称为空！', isOk: true, timeOut: 2000});
                                    return false;
                                }
                                if ($scope.forms.wareareaCode == '' || $scope.forms.wareareaCode == null || typeof ($scope.forms.wareareaCode) == "undefined") {
                                    tipDialog.open({title: 'Notice', template: '仓库库区编号为空！', isOk: true, timeOut: 2000});
                                    return false;
                                }
                                
                                //备件价格列表
                                var params = {warehouseCode: $scope.forms.warehouseCode, wareareaCode: $scope.forms.wareareaCode};
                                $http.get('/index.php?r=ReplacementPart/findSparePartsBackPrice', {params: params}).success(function(res) {
                                    if (res.ret == true) {
                                        var result = res.data;
                                        for (var i = 0; i < result.RPList.length; i++) {
                                            result.RPList[i]['isSelect'] = false;
                                            result.RPList[i]['aplyNum'] = 0;
                                        }
                                        $scope.RPList = result.RPList;

                                        if (result.RPList != '' && result.RPList != null && typeof (result.RPList) != "undefined") {
                                            //仓库保证金详情
                                            $http.get('/index.php?r=ReplacementPart/findDetailWarehouseFund', {params: params}).success(function(res) {
                                                if (res.ret == true) {
                                                    var result = res.data;
                                                    $scope.warehouseFund = result.warehouseFund.canUseAmount;
                                                    $scope.currency =  result.warehouseFund.currency;
                                                } else {
                                                    $scope.warehouseFund = 0;
                                                    tipDialog.open({title: 'Notice', template: res.errMsg, isOk: true, timeOut: 2000})
                                                }
                                            });
                                        }

                                    } else {
                                        tipDialog.open({title: 'Notice', template: res.errMsg, isOk: true, timeOut: 2000})
                                    }
                                });
                            };
                            

                            //前段sku,name过滤器
                            $scope.searchRPskuFilter = function(RPList) {
                                var keyword = new RegExp($scope.searchQus.goodsCode, 'i');
                                return !$scope.searchQus.goodsCode || keyword.test(RPList.goodsCode);
                            };
                            $scope.searchRPnameFilter = function(RPList) {
                                var keyword = new RegExp($scope.searchQus.goodsName, 'i');
                                return !$scope.searchQus.goodsName || keyword.test(RPList.goodsName);
                            };

                            //重置查询
                            $scope.resetSearchQuer = function() {
                                $scope.searchQus.goodsCode = '';
                                $scope.searchQus.goodsName = '';
                            };

                            $scope.isBorrowSwitch = function(srcObj) {
                                var $_this = $(srcObj.target);
                                if ($_this.prop('checked')) {
                                    $scope.forms.isBorrowSwitch = 1;
                                } else {
                                    $scope.forms.isBorrowSwitch = 0;
                                }
                            }

                            //选中置顶效果
                            $scope.topTRTAble = function(srcObj, code) {
                                var $_this = $(srcObj.target);
                                var code = $_this.val();
                                if ($_this.prop('checked')) {
                                    var isOver = $scope.selPriceTotal(code);
                                    if (!isOver) {
                                        $_this.prop('checked', false);
                                        return false;
                                    }
                                }

                                if ($_this.prop('checked')) {
                                    $scope.RPList = $scope.stick($scope.RPList, code, 'id', 1);
                                } else {
                                    $scope.RPList = $scope.stick($scope.RPList, code, 'id', 0);
                                    var price = 0;
                                    for (var i = 0; i < $scope.RPList.length; i++) {
                                        if ($scope.RPList[i]['isSelect']) {
                                            price += $scope.RPList[i]['price'] * $scope.RPList[i]['aplyNum'];
                                        }
                                    }
                                    $scope.selPrice = price;
                                }

                            };

                            //选中价格统计
                            $scope.selPriceTotal = function(code) {
                                var g = /^[1-9]*[1-9][0-9]*$/;
                                for (var i = 0; i < $scope.RPList.length; i++) {
                                    if ($scope.RPList[i]['id'] == code) {
                                        var aplyNum = $scope.RPList[i]['aplyNum'];
                                        if (!g.test(aplyNum)) {
                                            tipDialog.open({title: 'Notice', template: '申请数量必须大于0！', isOk: true, timeOut: 2000});
                                            return false;
                                        }
                                        if (aplyNum > $scope.RPList[i]['availableStock']) {
                                            tipDialog.open({title: 'Notice', template: '申请数量不能大于库存数量！', isOk: true, timeOut: 2000});
                                            return false;
                                        }
                                        $scope.RPList[i]['isSelect'] = true;
                                    }
                                }

                                var price = 0;
                                for (var i = 0; i < $scope.RPList.length; i++) {
                                    if ($scope.RPList[i]['isSelect']) {
                                        price += $scope.RPList[i]['price'] * $scope.RPList[i]['aplyNum'];
                                    }
                                }
                                $scope.selPrice = price;

                                return  true;
                            };

                            /**
                             * 
                             * @param {type} ary原数组
                             * @param {type} val选中字段值
                             * @param {type} key选中字段名称
                             * @param {type} type：0置底；1置顶
                             * @returns {unresolved}
                             */
                            $scope.stick = function(ary, val, key, type) {
                                var midAry = new Array();
                                var n = 0;
                                var isSelect = true;
                                if (type == 0) {
                                    isSelect = false;
                                    for (var i = 0; i < ary.length; i++) {
                                        if (ary[i][key] == val) {
                                            ary[i]['isSelect'] = isSelect;
                                            midAry = ary[i];
                                            ary.splice(i, 1);//删除元素
                                            ary.push(midAry);//末尾新增元素
                                            break;
                                        }
                                    }
                                } else {
                                    for (var i = 0; i < ary.length; i++) {
                                        if (ary[i][key] == val) {
                                            ary[i]['isSelect'] = isSelect;
                                            midAry = ary[i];
                                            ary.splice(i, 1);//删除元素
                                            ary.unshift(midAry);//开头新增元素
                                            break;
                                        }
                                    }
                                }
                                return ary;
                            }

                            $scope.returnApply = function() {
                                $scope.step = true;
                            }

                            //价格查询
                            $scope.cheeckPrice = function() {
                                var g = /^[1-9]*[1-9][0-9]*$/;
                                var list = [];
                                for (var i = 0; i < $scope.RPList.length; i++) {
                                    if ($scope.RPList[i]['isSelect']) {
                                        var aplyNum = $scope.RPList[i]['aplyNum'];
                                        if (!g.test(aplyNum)) {
                                            tipDialog.open({title: 'Notice', template: '申请数量必须是大于0的整数！', isOk: true, timeOut: 2000});
                                            return false;
                                        }
                                        list.push({
                                            goodsCode: $scope.RPList[i]['goodsCode'],
                                            count: $scope.RPList[i]['aplyNum'],
                                            currency:$scope.currency
                                        });
                                    }
                                }

                                if (list.length == 0) {
                                    tipDialog.open({title: 'Notice', template: '请勾选要出库的商品！', isOk: true, timeOut: 2000});
                                    return false;
                                }

                                $http.post('/index.php?r=replacementPart/findSparePartsApplyGoodsPrice4Return', {params: list}).success(function(res) {
                                    if (res.ret == true) {
                                        var result = res.data;
                                        var sparePartGoodsList = [];
                                        var selRePrice = 0;
                                        if (result.length > 0) {
                                            for (var i = 0; i < result.length; i++) {
                                                sparePartGoodsList.push({
                                                    goodsCode: result[i]['goodsCode'],
                                                    count: result[i]['count'],
                                                    goodsName: result[i]['goodsName'],
                                                    price: result[i]['price'],
                                                    currency: result[i]['currency'],
                                                    sourceId:result[i]['sourceId'],
                                                });
                                                
                                                selRePrice = selRePrice + (result[i]['price']*result[i]['count']);
                                            }
                                            $scope.forms.sparePartGoodsList = sparePartGoodsList;
                                            $scope.step = false;
                                            $scope.selRePrice = selRePrice;
                                            
                                        } else {
                                            tipDialog.open({title: 'Notice', template: '查询退料商品价格返回数据为空！', isOk: true, timeOut: 2000});
                                        }

                                    } else {
                                        tipDialog.open({title: 'Notice', template: res.errMsg, isOk: true, timeOut: 2000});
                                        return false;
                                    }
                                });
                            }

                            //提交
                            $scope.submit = function() {
                                var g = /^[1-9]*[1-9][0-9]*$/;
                                var sparePartGoodsList = $scope.forms.sparePartGoodsList;
                                if (sparePartGoodsList.length <= 0) {
                                    tipDialog.open({title: 'Notice', template: '请重新勾选要申请的商品！', isOk: true, timeOut: 2000});
                                    return false;
                                }

                                for (var i = 0; i < sparePartGoodsList.length; i++) {
                                    var aplyNum = $scope.forms.sparePartGoodsList[i]['count'];
                                    if (!g.test(aplyNum)) {
                                        tipDialog.open({title: 'Notice', template: '申请数量必须大于0！', isOk: true, timeOut: 2000});
                                        return false;
                                    }
                                }


                                if ($scope.forms.warehouseCode == '' || $scope.forms.warehouseCode == null || typeof ($scope.forms.warehouseCode) == "undefined") {
                                    tipDialog.open({title: 'Notice', template: '仓库编号为空！', isOk: true, timeOut: 2000});
                                    return false;
                                } 
                                if ($scope.forms.warehouseName == '' || $scope.forms.warehouseName == null || typeof ($scope.forms.warehouseName) == "undefined") {
                                    tipDialog.open({title: 'Notice', template: '仓库名称为空！', isOk: true, timeOut: 2000});
                                    return false;
                                } 

                                if ($scope.forms.wareareaCode == '' || $scope.forms.wareareaCode == null || typeof ($scope.forms.wareareaCode) == "undefined") {
                                    tipDialog.open({title: 'Notice', template: '仓库库区编号为空！', isOk: true, timeOut: 2000});
                                    return false;
                                }

                                //退料申请
                                $http.post('/index.php?r=ReplacementPart/addBackSparePartsApplySheet', {params: $scope.forms}).success(function(res) {
                                    if (res.ret == true) {
                                        if (typeof callBack == 'function') {
                                            callBack();
                                        }
                                        instance.close();
                                    } else {
                                        tipDialog.open({title: 'Notice', template: res.errMsg, isOk: true, timeOut: 2000})
                                    }
                                });
                            }
                        }
                    });
                }
            };
        })

