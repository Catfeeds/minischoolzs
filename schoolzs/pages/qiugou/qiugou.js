// pages/classify/classify.js
var app = getApp();
Page({
  data:{
    // 供求
    gongqiu:[],
  },

  jieshao:function(e){
    console.log(e.currentTarget.dataset.title)
    wx.navigateTo({
      url: '../synopsis/synopsis?title='+e.currentTarget.dataset.title,
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
    // 页面初始化 options为页面跳转所带来的参数
    var that = this;
    var uid = app.pubData.userId;
    wx.request({
      url: app.pubData.hostUrl + '/Api/User/my_qiu',
      method: 'post',
      data: { uid: uid },
      header: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      success: function (res) {
        var status = res.data.status;
        if (status == 1) {
          var list = res.data.list;
          that.setData({
            gongqiu: list,
          });
        } else {
          wx.showToast({
            title: res.data.err,
            duration: 2000
          });
        }
      },
      fail: function (e) {
        wx.showToast({
          title: '网络异常！',
          duration: 2000
        });
      },
    })
  },
  onReady:function(){
    // 页面渲染完成
  },
  onShow:function(){
    // 页面显示
  },
  onHide:function(){
    // 页面隐藏
  },
  onUnload:function(){
    // 页面关闭
  }
})