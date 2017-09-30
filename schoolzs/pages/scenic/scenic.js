// pages/scenic/scenic.js
var app = getApp();
//引入这个插件，使html内容自动转换成wxml内容
var WxParse = require('../../wxParse/wxParse.js');
Page({
  data:{
     text:'',
     imgUrls: [],
     indicatorDots: true,
     autoplay: true,
     interval:2000,
     duration: 1000,
     circular: true,
     info:{}
  },
  onLoad:function(options){
    // 页面初始化 options为页面跳转所带来的参数
    var that = this;
    //设置当前分类标题
    wx.setNavigationBarTitle({ title: options.title });

    var proId = options.proId;
    wx.request({
      url: app.pubData.hostUrl + '/Api/Product/details',
      method: 'post',
      data: { pro_id: proId },
      header: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      success: function (res) {
        var status = res.data.status;
        if (status == 1) {
          var content = res.data.content;
          var imgUrls = res.data.advimg;
          WxParse.wxParse('content', 'html', content, that, 5);
          that.setData({
            text: res.data.content,
            imgUrls: imgUrls,
            info: res.data.info
          });
        }else{
          wx.showToast({
            title: '商品不存在或已下架！',
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