// pages/classify/classify.js
var app = getApp();
Page({
  data:{
    // 供求
    page:2,
    catId:0,
    proData:[],
    keyword: '',
    no0:false,
    no1: false,
    no2: false,
    tabArr: {
      curHdIndex: '',
      curBdIndex: '',
    },
    tab:0,
    bei:false,
    dequ:'地区',
    huxing:'分类',
    paxing:'排序',
    jin:'价格',
    htype_list: [],
    hu_list:[],
    pai_list: [],
    price_list: [],
    addrId: 0,
    htypeId:0,
    priceId: 0,
    sortType:'',
  },
  // tab切换
  tabFun: function (e) {
    //获取触发事件组件的dataset属性 
    var _datasetId = e.target.dataset.id;
    if (_datasetId==0){
         this.setData({
           no0:true,
           no1:false,
           no2: false, no3: false,
           tab: 1
         })
         }
      if (_datasetId == 1) {
           this.setData({
             no1: true,
             no0: false,
             no2: false, no3: false,
             tab: 1
           })
   }
      if (_datasetId == 2) {
        this.setData({
          no2: true,
          no1: false,
          no0: false,
          no3: false,
          tab: 1
        })
      }
      if (_datasetId == 3) {
        this.setData({
          no3: true,
          no2: false,
          no1: false,
          no0: false,
          tab: 1
        })
      }

    var _obj = {};
    _obj.curHdIndex = _datasetId;
    _obj.curBdIndex = _datasetId;
    this.setData({
      tabArr: _obj,
      bei:true,
      tab: 1
    })
  },
kk:function(){
  this.setData({
    no0: false,
    no1: false,
    no2: false,
    no3:false,
     tab: 0
  });
},

//筛选项 地区 点击操作
filter0: function (e) {
  var that = this
  var addrId = e.currentTarget.dataset.id;
  var txt = e.currentTarget.dataset.txt;
  that.setData({
    dequ: txt,
    addrId: addrId
  });
  that.loadProData();
},

//筛选项 户型 点击操作
filter1: function (e) {
  var that = this
  var htypeId = e.currentTarget.dataset.id;
  var txt = e.currentTarget.dataset.txt;
  that.setData({
    huxing: txt,
    htypeId: htypeId
  });
  that.loadProData();
},

//筛选项 排序 点击操作
filter2: function (e) {
    var that=this
    var sortType = e.currentTarget.dataset.stype;
    var txt = e.currentTarget.dataset.txt;
    that.setData({
      paxing: txt,
      sortType: sortType
    });
    that.loadProData();
  },

//筛选项 价格 点击操作
filter3: function (e) {
  var that = this
  var priceId = e.currentTarget.dataset.id;
  var txt = e.currentTarget.dataset.txt;
  that.setData({
    jin: txt,
    priceId: priceId
  });
  that.loadProData();
},
jieshao:function(e){
    var proId = e.currentTarget.dataset.id;
    var title = e.currentTarget.dataset.title;
    wx.navigateTo({
      url: '../scenic/scenic?proId=' + proId + '&title=' + title,
    })
  },

  onLoad:function(options){
    // 页面初始化 options为页面跳转所带来的参数
    var that = this;
    var catId = options.catId;
    var title = options.title;
    var keyword = options.keyword;
    that.setData({
      catId: catId,
      keyword: keyword
    });

    //设置当前分类标题
    wx.setNavigationBarTitle({ title: title });
    //ajax请求数据
    wx.request({
      url: app.pubData.hostUrl + '/Api/Product/lists',
      method: 'post',
      data: {
        cat_id: catId,
        keyword: keyword
      },
      header: {
        'content-type': 'application/x-www-form-urlencoded'
      },
      success: function (res) {
        var shoplist = res.data.pro;
        var addr = res.data.addr;
        var htype = res.data.htype;
        var sortList = res.data.sortlist;
        var priceList = res.data.pricelist;
        that.setData({
          proData: shoplist,
          htype_list: addr,
          hu_list: htype,
          pai_list:sortList,
          price_list: priceList
        })
      },
      error: function (e) {
        wx.showToast({
          title: '网络异常！',
          duration: 2000
        });
      }
    })
  },

  loadProData: function (e) {
    // 页面初始化 options为页面跳转所带来的参数
    var that = this;
    var catId = that.data.catId;
    var keyword = that.data.keyword;

    //ajax请求数据
    wx.request({
      url: app.pubData.hostUrl + '/Api/Product/sortlist',
      method: 'post',
      data: {
        cat_id: catId,
        keyword: keyword,
        addrid: that.data.addrId,
        htype: that.data.htypeId,
        sorttype: that.data.sortType,
        priceId: that.data.priceId,
      },
      header: {
        'content-type': 'application/x-www-form-urlencoded'
      },
      success: function (res) {
        var shoplist = res.data.pro;
        that.setData({
          proData: shoplist,
        })
      },
      error: function (e) {
        wx.showToast({
          title: '网络异常！',
          duration: 2000
        });
      }
    })
  },

  //点击加载更多
  getMore: function (e) {
    var that = this;
    var page = that.data.page;
    wx.request({
      url: app.pubData.hostUrl + '/Api/Product/get_more',
      method: 'post',
      data: {
        page: page,
        cat_id: that.data.catId,
        keyword: that.data.keyword,
        addrid: that.data.addrId,
        htype: that.data.htypeId,
        sorttype: that.data.sortType,
        priceId: that.data.priceId,
      },
      header: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      success: function (res) {
        var pro = res.data.pro;
        if (pro == '') {
          wx.showToast({
            title: '没有更多数据！',
            duration: 2000
          });
          return false;
        }
        //that.initProductData(data);
        that.setData({
          page: page + 1,
          proData: that.data.proData.concat(pro)
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

  calltel: function (e) {
    var tel = e.currentTarget.dataset.tel;
    wx.makePhoneCall({
      phoneNumber: tel, //此号码为真实电话号码
      success: function () {
        console.log("拨打电话成功！")
      },
      fail: function () {
        console.log("拨打电话失败！")
      }
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