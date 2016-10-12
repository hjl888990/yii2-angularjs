'use strict';
/* collect Controllers */
//知识库分类管理
app.controller('knowledgeCatController', function($scope, $http,tipDialog) {
    
    $scope.storeId = '';
    $scope.categorys = '';
    var timeOut = 3000;
    //中英文切换
    if ($scope.selectLang === "English") {
       var Notice = 'Notice';
       var SuccessMsg = 'Successfully!';
    } else {
       var Notice = '提示信息';
       var SuccessMsg = '保存成功!';
    }

    //初始化执行函数展示顶端分类
    $scope.load = function() {
        $http.get('/index.php?r=knowledge-category/ajax-knowledge-form-data').success(function(res) {
            if (res.ret == true) {
                var result = res.data;
                if(result.knowledgeStoreIds != null && result.knowledgeStoreIds != 'null'){
                    $scope.storeId = result.knowledgeStoreIds[0]['id'];
                    $scope.chooseKt($scope.storeId,0); 
                }
                $scope.storeArr = result.knowledgeStoreIds;
                
            } else {
                tipDialog.open({title: Notice, template: res.errMsg, isOk: true, timeOut: timeOut})
            }
        });
    }

    //选择来源库查询
    $scope.chooseKt = function(storeid,parentid) {
        $scope.storeId = storeid;
        var params = {storeid: storeid,parentid:parentid};
        $http.get('/index.php?r=knowledge-category/ajax-knowledge-cat', {params: params}).success(function(res) {
            if (res.ret == true) {
                var result = res.data;
                var categorys = result.data;
                angular.forEach(categorys, function(v,k){
                    categorys[k]['show'] = true;
                });
                $scope.categorys = categorys;
            } else {
                tipDialog.open({title: Notice, template: res.errMsg, isOk: true, timeOut: timeOut})
            }
        })
    }

   /**
     * 打开添加弹窗
     * @returns {undefined}
     */
    $scope.add = function(parentName,parentid,level) {
        $scope.catCreateForm = {title: '',parentName:parentName,parentid:parentid,storeid:$scope.storeId,level:level};
        $scope.createReturnMessage= {title: ''};
        easyDialog.open({
            container: 'createKnowlegdeCatId', //弹窗元素id
            // autoClose:10000,//自动关闭
            fixed: false,
        });
    }
    /**
     * 添加分类
     * @returns {undefined}
     */
    $scope.createSubmit = function() {
        if($scope.catCreateForm.title == '' || $scope.catCreateForm.title == 'null' || $scope.catCreateForm.title == null){
            $scope.createReturnMessage.title = '分类名称不能为空.';
            return false;
        }
        var param = {
            catCreateForm: $scope.catCreateForm
        };
        $http.post('/index.php?r=knowledge-category/ajax-create-cat', param).success(function(res) {
            if (res.ret === 1) {
                $scope.closeEasyDialog();
                tipDialog.open({title: Notice, template: SuccessMsg, isOk: true, timeOut: timeOut})
                $scope.chooseKt($scope.storeId,0);
            } else {
                if (res.ret === 0) {
                    $scope.createReturnMessage = res.data;
                } else {
                    $scope.closeEasyDialog();
                    tipDialog.open({title: Notice, template: res.errMsg, isOk: true, timeOut: timeOut})
                }
            }
        });
    }
    
    /**
     * 打开编辑弹窗
     * @returns {undefined}
     */
    $scope.edit = function(id,title) {
        $scope.catUpdateForm = {id: id,title:title};
        $scope.createReturnMessage= {title: ''};
        easyDialog.open({
            container: 'updateKnowlegdeCatId', //弹窗元素id
            // autoClose:10000,//自动关闭
            fixed: false,
        });
    }
    
    /**
     * 编辑分类
     * @returns {undefined}
     */
    $scope.updateSubmit = function() {
        if($scope.catUpdateForm.title == '' || $scope.catUpdateForm.title == 'null' || $scope.catUpdateForm.title == null){
            $scope.createReturnMessage.title = '分类名称不能为空.';
            return false;
        }
        var param = {
            catUpdateForm: $scope.catUpdateForm
        };
        $http.post('/index.php?r=knowledge-category/ajax-update-cat', param).success(function(res) {
            if (res.ret === 1) {
                $scope.closeEasyDialog();
                tipDialog.open({title: Notice, template: SuccessMsg, isOk: true, timeOut: timeOut})
                $scope.chooseKt($scope.storeId,0);
            } else {
                if (res.ret === 0) {
                    $scope.createReturnMessage = res.data;
                } else {
                    $scope.closeEasyDialog();
                    tipDialog.open({title: Notice, template: res.errMsg, isOk: true, timeOut: timeOut})
                }
            }
        });
    }
    
    
    
    /**
     * 关闭用户添加弹窗
     * @returns {Boolean}
     */
    $scope.closeEasyDialog = function() {
        $scope.catCreateForm = {title: '',parentName:'',parentid:'',storeid:$scope.storeId,level:''};
        $scope.catUpdateForm = {id: '',title:''};
        easyDialog.close();
        return true;
    }


    //展开分类
    $scope.showSonCat = function(categoryId) {
        var storeID = vm.storeId;
        var params = {categoryId: categoryId, storeID: storeID};

        var hasMe = 0;
        for (var i = 0; i < vm.topCategory.length; i++) {
            if (vm.topCategory[i].parentId == categoryId) {
                hasMe = 1;
            }
        }

        if (hasMe == 0) {//没有查询记录
            $http.get('/index.php?r=customerNlg/getChildCategory', {params: params}).success(function(res) {
                if (res.ret == true) {
                    var result = res.data;
                    $scope.topCategory = result.topCategory;

                    var catArr = new Array();
                    for (var i = 0; i < vm.topCategory.length; i++) {
                        if (vm.topCategory[i].categoryId == categoryId) {
                            vm.topCategory[i].moreless = 0;
                            catArr.push(vm.topCategory[i]);
                            for (var j = 0; j < $scope.topCategory.length; j++) {
                                catArr.push($scope.topCategory[j]);
                            }
                        } else {
                            catArr.push(vm.topCategory[i]);
                        }
                    }
                    vm.topCategory = catArr;

                } else {
                    tipDialog.open({title: Notice, template: res.errMsg, isOk: true})
                }
            });
        } else {//有查询记录
            for (var i = 0; i < vm.topCategory.length; i++) {
                if (vm.topCategory[i].parentId == categoryId) {
                    vm.topCategory[i].is_show = 1;
                }
                if (vm.topCategory[i].categoryId == categoryId) {
                    vm.topCategory[i].moreless = 0;
                }
            }
        }

    }

    //隐藏分类
    $scope.hideSonCat = function(cparentID) {
        var params = {categorys: vm.topCategory, cparentID: cparentID};
        $http.post('/index.php?r=customerNlg/getAllChildCategory', {params: params}).success(function(res) {
            if (res.ret == true) {
                var result = res.data;
                $scope.topCategory = result.topCategory;

                var sonArry = new Array();
                for (var i = 0; i < $scope.topCategory.length; i++) {
                    sonArry.push($scope.topCategory[i].categoryId);
                }

                for (var i = 0; i < vm.topCategory.length; i++) {
                    if (vm.topCategory[i].categoryId == cparentID) {
                        vm.topCategory[i].moreless = 1;
                    }

                    if ($scope.contains(sonArry, vm.topCategory[i].categoryId)) {
                        vm.topCategory[i].is_show = 0;
                        vm.topCategory[i].moreless = 1;
                    }
                }

            } else {
                tipDialog.open({title: Notice, template: res.errMsg, isOk: true})
            }
        });
    }

    /**
     * 数组查找
     * @param {type} arr
     * @param {type} val
     * @returns {Boolean}
     */
    $scope.contains = function(arr, val) {
        for (var i = 0; i < arr.length; i++) {
            if (arr[i] === val) {
                return true;
            }
        }
    }

    //删除分类
    $scope.delect = function(categoryID) {
        if (confirm(msg_sfsc)) {
            var storeID = vm.storeId;
            var params = {categoryID: categoryID, storeID: storeID};
            $http.get('/index.php?r=customerNlg/delCategory', {params: params}).success(function(res) {
                if (res.ret == true) {
                    var result = res.data;
                    $scope.topCategory = result.topCategory;
                    vm.topCategory = $scope.topCategory;
                    tipDialog.open({title: Notice, template: almsg, isOk: true, timeOut: 3000})
                } else {
                    tipDialog.open({title: Notice, template: res.errMsg, isOk: true, timeOut: 3000})
                }
            });
        }

    }


    //分类上移
    $scope.up = function(srcObj) {
        var $_this = $(srcObj.target);

        var jqtr = $($_this).parents("tr");
        var srcID, storeID, parentID;
        var targetID;

        srcID = jqtr.attr("cid");
        storeID = jqtr.attr("storeID");
        parentID = jqtr.attr("cparentID");
        //有没有上一个tr?

        if (jqtr.prevAll("tr[cparentID='" + parentID + "']").length < 1) {
            tipDialog.open({title: Notice, template: msg_istop, isOk: true, timeOut: 3000})
            return false;
        }
        var prevBrotherTr = jqtr.prevAll("tr[cparentID='" + parentID + "']");
        targetID = prevBrotherTr.attr("cid");

        var params = {srcID: srcID, storeID: storeID, targetID: targetID};
        $http.get('/index.php?r=customerNlg/swapCategoryOrder', {params: params}).success(function(res) {
            if (res.ret == true) {
                var result = res.data;
                $scope.topCategory = result.topCategory;
                vm.topCategory = $scope.topCategory;
                tipDialog.open({title: Notice, template: almsg, isOk: true, timeOut: 3000})
            } else {
                tipDialog.open({title: Notice, template: res.errMsg, isOk: true, timeOut: 3000})
            }
        });

    }

    //分类上移顶部
    $scope.upTop = function(srcObj) {
        var $_this = $(srcObj.target);

        var jqtr = $($_this).parents("tr");
        var srcID, storeID, parentID;
        var targetID;

        srcID = jqtr.attr("cid");
        storeID = jqtr.attr("storeID");
        parentID = jqtr.attr("cparentID");
        //有没有上一个tr?
        if (jqtr.prevAll("tr[cparentID='" + parentID + "']").length < 1) {
            tipDialog.open({title: Notice, template: msg_istop, isOk: true, timeOut: 3000})
            return false;
        }

        var firstTr = $("tr[cparentID='" + parentID + "']").get(0);
        targetID = $(firstTr).attr("cid");

        var params = {srcID: srcID, storeID: storeID, targetID: targetID};
        $http.get('/index.php?r=customerNlg/swapCategoryOrder', {params: params}).success(function(res) {
            if (res.ret == true) {
                var result = res.data;
                $scope.topCategory = result.topCategory;
                vm.topCategory = $scope.topCategory;
                tipDialog.open({title: Notice, template: almsg, isOk: true, timeOut: 3000})
            } else {
                tipDialog.open({title: Notice, template: res.errMsg, isOk: true, timeOut: 3000})
            }
        });

    }

    //分类下移
    $scope.down = function(srcObj) {
        var $_this = $(srcObj.target);
        var jqtr = $($_this).parents("tr");
        var srcID, storeID, parentID;
        var targetID;

        srcID = jqtr.attr("cid");
        storeID = jqtr.attr("storeID");
        parentID = jqtr.attr("cparentID");
        //有没有上一个tr?

        if (jqtr.nextAll("tr[cparentID='" + parentID + "']").length < 1) {
            tipDialog.open({title: Notice, template: msg_isbum, isOk: true, timeOut: 3000})
            return false;
        }
        var nextBrotherTr = jqtr.nextAll("tr[cparentID='" + parentID + "']");
        targetID = nextBrotherTr.attr("cid");

        var params = {srcID: srcID, storeID: storeID, targetID: targetID};
        $http.get('/index.php?r=customerNlg/swapCategoryOrder', {params: params}).success(function(res) {
            if (res.ret == true) {
                var result = res.data;
                $scope.topCategory = result.topCategory;
                vm.topCategory = $scope.topCategory;
                tipDialog.open({title: Notice, template: almsg, isOk: true, timeOut: 3000})
            } else {
                tipDialog.open({title: Notice, template: res.errMsg, isOk: true, timeOut: 3000})
            }
        });

    }

    //分类下移到底部
    $scope.downBum = function(srcObj) {
        var $_this = $(srcObj.target);

        var jqtr = $($_this).parents("tr");
        var srcID, storeID, parentID;
        var targetID;

        srcID = jqtr.attr("cid");
        storeID = jqtr.attr("storeID");
        parentID = jqtr.attr("cparentID");
        //有没有上一个tr?

        if (jqtr.nextAll("tr[cparentID='" + parentID + "']").length < 1) {
            tipDialog.open({title: Notice, template: msg_isbum, isOk: true, timeOut: 3000})
            return false;
        }

        var allTr = $("tr[cparentID='" + parentID + "']");
        var lastTr = allTr.get(allTr.length - 1);
        targetID = $(lastTr).attr("cid");

        var params = {srcID: srcID, storeID: storeID, targetID: targetID};
        $http.get('/index.php?r=customerNlg/swapCategoryOrder', {params: params}).success(function(res) {
            if (res.ret == true) {
                var result = res.data;
                $scope.topCategory = result.topCategory;
                vm.topCategory = $scope.topCategory;
                tipDialog.open({title: Notice, template: almsg, isOk: true, timeOut: 3000})
            } else {
                tipDialog.open({title: Notice, template: res.errMsg, isOk: true, timeOut: 3000})
            }
        });
    }
})
