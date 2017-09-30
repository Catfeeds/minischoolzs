//index.js
//获取应用实例
var bmap = require('../budu-map/bmap-wx.min.js'); 
var app = getApp();
var wxMarkerData = [];

Page({
  data: {
    tabArr: { 
      curHdIndex: 0, 
      curBdIndex: 0 
    }, 
    markers: [],  
    imgUrls: [], //轮播图
    'address':'定位中',
    proCat:[],  //产品分类
    page:2,
    // 供求
    gong:[],
    qiu:[],
    productData:[],
    news:[],
    dtype:1,
    ak:"AXMRrsEZ0CGfogyRENeexOTkHxauhZtz",   //填写申请到的ak 
    indicatorDots: true,
    autoplay: true,
    interval: 4000,
    duration: 1000,
    hasLocation: false,
    gongpage:1,
    qiupage:1,
    keyword:'',
    imgs:'',
    img_save:''
  },
  hidden_3:function (e) {
    console.log(e)
  },
  // 上传图片
  chooseImage: function () {
    var that = this
    wx.chooseImage({
      count: 1,
      sizeType: ['original', 'compressed'], // 可以指定是原图还是压缩图，默认二者都有  
      sourceType: ['album', 'camera'], // 可以指定来源是相册还是相机，默认二者都有
      success: function (res) {
        var imageSrc = res.tempFilePaths[0];
        
         that.setData({
           imgs: imageSrc
         })
          }
        })

      },
  //搜索
  sou:function(e){
    var keyword = this.data.keyword;
    console.log(keyword);
    wx.navigateTo({
      url: '../classify/classify?title=搜索&keyword=' + keyword,
    })
  },

  inputTyping:function(e){
    var keyword = e.detail.value;
    if(keyword){
      this.setData({
        keyword: keyword
      });
    }
  },

  //接单
  jie:function(e) {
    var id = e.currentTarget.dataset.id;
    var that = this;
    wx.showModal({
      title: '提示',
      content: '是否确定要接单？',
      success: function (res) {
        res.confirm && wx.request({
          url: app.pubData.hostUrl + '/Api/User/orders',
          method: 'post',
          data: {
            sid: id,
            uid: app.pubData.userId
          },
          header: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          success: function (res) {
            //--init data
            var status = res.data.status;
            if (status == 1) {
              wx.showToast({
                title: '接单成功，请及时联系该会员！',
                duration: 2500
              });
              that.initIndexData();
            } else {
              wx.showToast({
                title: res.data.err,
                duration: 2500
              });
            }
          },
          fail: function () {
            // fail
            wx.showToast({
              title: '网络异常！',
              duration: 2000
            });
          }
        });
      }
    });
  },

  //联系
  lian: function (e) {
    var that = this;
    var id = e.currentTarget.dataset.id;
    var phone = e.currentTarget.dataset.phone;
    wx.makePhoneCall({
      phoneNumber: phone, //此号码并非真实电话号码，仅用于测试
      success: function () {
        //修改状态
        wx.request({
          url: app.pubData.hostUrl + '/Api/User/contact',
          method: 'post',
          data: {  
            uid:app.pubData.userId,
            id:id
          },
          header: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          success: function (res) {
            that.initIndexData();
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
      fail: function () {
        console.log("拨打电话失败！")
      }
    })
  },

  //上一页
  lastpage: function (e) {
    var that = this;
    var ptype = e.currentTarget.dataset.ptype;
    if(ptype==1){
      var page = that.data.gongpage;
    }else{
      var page = that.data.qiupage;
    }
    if(page<=1){
      wx.showToast({
        title: '已经是第一页了！',
        duration: 2000
      });
      return false;
    }
    wx.request({
      url: app.pubData.hostUrl + '/Api/Index/getpage',
      method: 'post',
      data: { page: page-1, ptype: ptype },
      header: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      success: function (res) {
        var list = res.data.list;
        if (list == '') {
          wx.showToast({
            title: '没有找到更多数据',
            duration: 2000
          });
          return false;
        }
        if (ptype == 1) {
          that.setData({
            gong: list,
            gongpage: page - 1
          });
        } else {
          that.setData({
            qiu: list,
            qiupage: page - 1
          });
        }
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

  //下一页
  nextpage: function (e) {
    var that = this;
    var ptype = e.currentTarget.dataset.ptype;
    if(ptype == 1){
      var page = that.data.gongpage;
    }else{
      var page = that.data.qiupage;
    }
    wx.request({
      url: app.pubData.hostUrl + '/Api/Index/getpage',
      method: 'post',
      data: { page: page+1, ptype: ptype },
      header: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      success: function (res) {
        var list = res.data.list;
        if (list == '') {
          wx.showToast({
            title: '已经是最后一页了！',
            duration: 2000
          });
          return false;
        }
        if (ptype == 1) {
          that.setData({
            gong: list,
            gongpage: page + 1
          });
        } else {
          that.setData({
            qiu: list,
            qiupage: page + 1
          });
        }
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

  //我要发布
  bindFormSubmit:function(e){
    var that = this;
    var content = e.detail.value.content;
    var dtype = that.data.dtype;
    if(!content){
      wx.showToast({
        title: '请输入供求内容！',
        duration: 2000
      });
      return false;
    }
    console.log(dtype)
    if (dtype < 1 || dtype > 2){
      wx.showToast({
        title: '网络异常，请稍后再试！',
        duration: 2000
      });
      return false;
    }
    var imageSrc = that.data.imgs;
    console.log(imageSrc)
    if (imageSrc !='../../image/sssss.png'){
      wx.uploadFile({
        url: app.pubData.hostUrl + '/Api/User/uploadimg',
        filePath: imageSrc,
        name: 'data',
        uid: app.globalData.userInfo.id,
        success: function (res) {
          console.log('uploadImage success, res is:', res);
          that.setData({
            img_save: res.data,
          });
          wx.request({
            url: app.pubData.hostUrl + '/Api/User/supply',
            method: 'post',
            data: {
              content: content,
              dtype: dtype,
              uid: app.pubData.userId,
              photo_x: that.data.img_save
            },
            header: {
              'Content-Type': 'application/x-www-form-urlencoded'
            },
            success: function (res) {
              var status = res.data.status;
              if (status == 1) {
                wx.showToast({
                  title: '发布成功！',
                  duration: 2000
                });
                that.initIndexData();
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
        fail: function ({ errMsg }) {
          console.log('uploadImage fail, errMsg is', errMsg)
        }
      })
    }else{
      wx.request({
        url: app.pubData.hostUrl + '/Api/User/supply',
        method: 'post',
        data: {
          content: content,
          dtype: dtype,
          uid: app.pubData.userId,
          photo_x: that.data.img_save
        },
        header: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        success: function (res) {
          var status = res.data.status;
          if (status == 1) {
            wx.showToast({
              title: '发布成功！',
              duration: 2000
            });
            that.initIndexData();
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
    }
    
    
  },

  // 弹窗
  setModalStatus: function (e) {
    var dtype = e.currentTarget.dataset.dtype;
    this.setData({
      dtype: dtype
    });
    var animation = wx.createAnimation({
      duration: 200,
      timingFunction: "linear",
      delay: 0
    })
    this.animation = animation
    animation.translateY(300).step()
    this.setData({
      animationData: animation.export()
    })
    if (e.currentTarget.dataset.status == 1) {
      this.setData(
        {
          showModalStatus: true
        }
      );
    }
    setTimeout(function () {
      animation.translateY(0).step()
      this.setData({
        animationData: animation
      })
      if (e.currentTarget.dataset.status == 0) {
        this.setData(
          {
            showModalStatus: false
          }
        );
      }
    }.bind(this), 200)
  },

//加载首页数据
initIndexData:function(){
  var that = this;
  wx.request({
    url: app.pubData.hostUrl + '/Api/Index/index',
    method: 'post',
    data: {},
    header: {
      'Content-Type': 'application/x-www-form-urlencoded'
    },
    success: function (res) {
      var ggtop = res.data.ggtop;
      var cat = res.data.cat;
      var prolist = res.data.prolist;
      var news = res.data.news;
      var gong = res.data.gong;
      var qiu = res.data.qiu;
      //that.initProductData(data);
      that.setData({
        imgUrls: ggtop,
        proCat: cat,
        productData: prolist,
        news: news,
        gong: gong,
        qiu: qiu
      });
      //endInitData
    },
    fail: function (e) {
      wx.showToast({
        title: '网络异常！err:index',
        duration: 2000
      });
    },
  })
},

//热门资讯 加载更多
loadMore:function(e){
  var that = this;
  var page = that.data.page;
  wx.request({
    url: app.pubData.hostUrl + '/Api/Index/getlist',
    method: 'post',
    data: { page: page},
    header: {
      'Content-Type': 'application/x-www-form-urlencoded'
    },
    success: function (res) {
      var news = res.data.news;
      if (news==''){
        wx.showToast({
          title: '没有找到更多数据',
          duration: 2000
        });
        return false;
      }
      that.setData({
        news: that.data.news.concat(news),
        page: page+1
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

// 分类点击
feilei:function(e){
    var title = e.currentTarget.dataset.title;
    var id = e.currentTarget.dataset.id;
    wx.navigateTo({
      url: '../classify/classify?title=' + title+'&catId='+id,
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

// 景区
jing:function(e){
    var id = e.currentTarget.dataset.id;
    wx.navigateTo({
      url: '../scenic/scenic?proId='+id,
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

// 资讯
jumpDetails:function(e){
    var newsId = e.currentTarget.dataset.id;
    wx.navigateTo({
      url: '../news/news?newsId=' + newsId,
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

// tab切换
tabFun: function(e){ 
 //获取触发事件组件的dataset属性 
 var _datasetId=e.target.dataset.id; 
 var _obj={}; 
 _obj.curHdIndex=_datasetId; 
 _obj.curBdIndex=_datasetId; 
 this.setData({ 
  tabArr: _obj 
 }); 
}, 
  changeIndicatorDots: function(e) {
    this.setData({
      indicatorDots: !this.data.indicatorDots
    })
  },
  changeAutoplay: function(e) {
    this.setData({
      autoplay: !this.data.autoplay
    })
  },
  intervalChange: function(e) {
    this.setData({
      interval: e.detail.value
    })
  },
  durationChange: function(e) {
    this.setData({
      duration: e.detail.value
    })
  },
  onLoad:function(options){  
    var that = this;  
    /* 获取定位地理位置 */  
    // 新建bmap对象   
    var BMap = new bmap.BMapWX({   
        ak: that.data.ak,
    });   
        //console.log(BMap)    
    var fail = function(data) {   
        console.log(data);  
    };   
    var success = function(data) {   
        //返回数据内，已经包含经纬度  
        console.log(data);  
        //使用wxMarkerData获取数据  
        //  = data.wxMarkerData;  
wxMarkerData=data.originalData.result.addressComponent.city
        //把所有数据放在初始化data内  
        console.log(wxMarkerData)
        that.setData({   
            // markers: wxMarkerData,
            // latitude: wxMarkerData[0].latitude,  
            // longitude: wxMarkerData[0].longitude,  
            address: wxMarkerData 
        });  
    }   
    // 发起regeocoding检索请求   
    BMap.regeocoding({   
        fail: fail,   
        success: success  
    }); 
    //加载首页数据
    that.initIndexData();      
  },

  //分享
  onShareAppMessage: function () {
    return {
      title: '二手房直卖网',
      path: '/pages/index/index',
      success: function (res) {
        // 分享成功
      },
      fail: function (res) {
        // 分享失败
      }
    }
  }

})



