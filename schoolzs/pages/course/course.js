// var course = require( "../../util/course" );
// pages/course/course.js
var app = getApp();
Page({
  data:{
    courses:[],
    mainList:[],
    cLass : '',
    coursesInd : 0,
    page:2
  },
qiye:function(e){
    var shopId = e.currentTarget.dataset.id;
    wx.navigateTo({
      url: '../synopsis/synopsis?shopId=' + shopId,
      success: function(res){
        // success
      },
      fail: function() {
        // fail
      },
      complete: function() {
        // complete
      }
    })
  },
  onLoad:function(options){
    var that = this;
    wx.request({
      url: app.pubData.hostUrl + '/Api/Shangchang/index',
      method: 'post',
      data: {},
      header: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      success: function (res) {
        var store_list = res.data.store_list;
        var catlist = res.data.catlist;
        //that.initProductData(data);
        that.setData({
          mainList: store_list,
          courses: catlist
        });
        //endInitData
      },
      fail: function (e) {
        wx.showToast({
          title: '网络异常！',
          duration: 2000
        });
      },
    })
  },

  //获取单个分类下的企业
  changeList (e){
    var that = this;
    var index = e.currentTarget.dataset.index;
    var coursesInd = that.data.coursesInd;
    if (index == coursesInd){
      return false;
    }
    that.setData({
      coursesInd : index
    })
    wx.request({
      url: app.pubData.hostUrl + '/Api/Shangchang/index',
      method: 'post',
      data: { cid: index},
      header: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      success: function (res) {
        var store_list = res.data.store_list;
        if (store_list==''){
          wx.showToast({
            title: '该分类下没有数据！',
            duration: 2000
          });
          return false;
        }
        //that.initProductData(data);
        that.setData({
          mainList: store_list,
          page:2
        });
        //endInitData
      },
      fail: function (e) {
        wx.showToast({
          title: '网络异常！',
          duration: 2000
        });
      },
    })
  },

  //点击加载更多
  getMore: function (e) {
    var that = this;
    var page = that.data.page;
    wx.request({
      url: app.pubData.hostUrl + '/Api/Shangchang/get_more',
      method: 'post',
      data: {
        page: page,
        cid: that.data.coursesInd
      },
      header: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      success: function (res) {
        var store_list = res.data.store_list;
        if (store_list == '') {
          wx.showToast({
            title: '没有更多数据！',
            duration: 2000
          });
          return false;
        }
        //that.initProductData(data);
        that.setData({
          page: page + 1,
          mainList: that.data.mainList.concat(store_list)
        });
        //endInitData
      },
      fail: function (e) {
        wx.showToast({
          title: '网络异常！',
          duration: 2000
        });
      }
    })
  },

  show (){
      this.setData({
          cLass : "animate"
      })
  },
  hide (){
      this.setData({
          cLass : ''
      })
  }
})