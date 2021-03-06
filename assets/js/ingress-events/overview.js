const $ = require('jquery')
require('jsrender')($)
$.views.settings.delimiters('<%', '%>')

const jsData = $('#js-data')
const eventIds = jsData.data('event-ids')

for (let i in eventIds) {
    let out = $('#event-' + eventIds[i])
    out.html('<div class="spinner-border text-success"></div>')
    $.ajax({
        url: '/ingress/event/fetch-overview/'+ eventIds[i],
        success: function (result) {
            const tmpl = $.templates('#myTemplate')
            const html = tmpl.render(result)
            out.html(html)
            updateTotal()
        }, error: function(xhr){
            out.html("An error occured: " + xhr.status + " " + xhr.statusText)
        }
    })
}

function updateTotal() {
    const outEnl = $('#total-enl')
    const outRes = $('#total-res')
    let totalEnl = 0
    let totalRes = 0
    $('.counter').each(function(){
        if ($(this).hasClass('faction-enl')) {
            totalEnl += parseInt($(this).html())
        } else {
            totalRes += parseInt($(this).html())
        }
    })
    outEnl.html(totalEnl)
    outRes.html(totalRes)
    $('#total').html(parseInt(outEnl.html()) + parseInt(outRes.html()))
}

