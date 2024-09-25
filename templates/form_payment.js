console.log("form admin loaded")
// alert("form admin loaded")



class FlutterwaveHooks {
    constructor() {
        this.domLoaded = false;
        this.scriptsLoaded = false;
        this.hooks = {action: {}, filter: {}};
    }
    initializeOnLoaded(o) {
        this.domLoaded && this.scriptsLoaded ? o() : !this.domLoaded && this.scriptsLoaded ? window.addEventListener("DOMContentLoaded", o) : document.addEventListener("gform_main_scripts_loaded", o)
    }
    addAction(o, n, r, t) {
        this.addHook("action", o, n, r, t)
    }
    addFilter(o, n, r, t) {
        this.addHook("filter", o, n, r, t)
    }
    doAction(o) {
        this.doHook("action", o, arguments)
    }
    applyFilters(o) {
        return this.doHook("filter", o, arguments)
    }
    removeAction(o, n) {
        this.removeHook("action", o, n)
    }
    removeFilter(o, n, r) {
        this.removeHook("filter", o, n, r)
    }
    addHook(o, n, r, t, i) {
        null == this.hooks[o][n] && (this.hooks[o][n] = []);
        var e = this.hooks[o][n];
        null == i && (i = n + "_" + e.length),
        this.hooks[o][n].push({
            tag: i,
            callable: r,
            priority: t = null == t ? 10 : t
        })
    }
    doHook(n, o, r) {
        var t;
        if (r = Array.prototype.slice.call(r, 1),
        null != this.hooks[n][o] && ((o = this.hooks[n][o]).sort(function(o, n) {
            return o.priority - n.priority
        }),
        o.forEach(function(o) {
            "function" != typeof (t = o.callable) && (t = window[t]),
            "action" == n ? t.apply(null, r) : r[0] = t.apply(null, r)
        })),
        "filter" == n)
            return r[0]
    }
    removeHook(o, n, t, i) {
        var r;
        null != this.hooks[o][n] && (r = (r = this.hooks[o][n]).filter(function(o, n, r) {
            return !!(null != i && i != o.tag || null != t && t != o.priority)
        }),
        this.hooks[o][n] = r)
    }
}

class FlutterwaveSettings extends FlutterwaveHooks {
    constructor() {
        super();
        this.init_payment();
    }
    init_payment() {
        var script = document.createElement('script');
        script.src = 'https://checkout.flutterwave.com/v3.js';
        script.onload = function() {
            this.init_function();
        }
        document.body.appendChild(script);
    }
    init_function() {
        // 
    }
    makePayment() {
        FlutterwaveCheckout({
          public_key: "FLWPUBK_TEST-SANDBOXDEMOKEY-X",
          tx_ref: "titanic-48981487343MDI0NzMx",
          amount: 54600,
          currency: "NGN",
          payment_options: "card, mobilemoneyghana, ussd",
          redirect_url: "https://glaciers.titanic.com/handle-flutterwave-payment",
          meta: {
            consumer_id: 23,
            consumer_mac: "92a3-912ba-1192a",
          },
          customer: {
            email: "rose@unsinkableship.com",
            phone_number: "08102909304",
            name: "Rose DeWitt Bukater",
          },
          customizations: {
            title: "The Titanic Store",
            description: "Payment for an awesome cruise",
            logo: "https://www.logolynx.com/images/logolynx/22/2239ca38f5505fbfce7e55bbc0604386.jpeg",
          }
        });
    }
}
new FlutterwaveSettings();