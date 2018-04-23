var municipios = [];
var ticks_res = [];

var municipios_avg = [];
var ticks_res_avg = [];

$(document).ready(function () {
    getChart1();
});

function getChart1() {
    $.ajax({
        url: "include/Libs.php?accion=showChart1",
        type: 'POST',
        dataType: 'JSON',
        error: function (){
            bootbox.alert("Experimentamos fallas técnicas. Comuníquese con su proveedor.");
        }, success: function (result) {
            municipios = result.municipio;
            ticks_res = result.ticks;

            var plot = $.plot($("#chart_1"), [{
                            data: municipios,
                            label: "Cantidad de Clientes por Municipio"
                        }
                    ], {
                        series: {
                            lines: {
                                show: true,
                                lineWidth: 2,
                                fill: true,
                                fillColor: {
                                    colors: [{
                                            opacity: 0.05
                                        }, {
                                            opacity: 0.01
                                        }
                                    ]
                                }
                            },
                            points: {
                                show: true
                            },
                            shadowSize: 2
                        },
                        grid: {
                            hoverable: true,
                            clickable: true,
                            tickColor: "#eee",
                            borderWidth: 0
                        },
                        colors: ["#DB5E8C", "#F0AD4E", "#5E87B0"],
                        xaxis: {
                            ticks:  ticks_res
                        },
                        yaxis: {
                            ticks: 11,
                            tickDecimals: 0
                        }
                    });

                var previousPoint = null;
                $("#chart_1").bind("plothover", function (event, pos, item) {
                    $("#x").text(pos.x.toFixed(2));
                    $("#y").text(pos.y.toFixed(2));

                    if (item) {
                        if (previousPoint != item.dataIndex) {
                            previousPoint = item.dataIndex;

                            $("#tooltip").remove();
                            var x = item.datapoint[0].toFixed(2),
                                y = item.datapoint[1].toFixed(2);

                            showTooltip(item.pageX, item.pageY, y);
                        }
                    } else {
                        $("#tooltip").remove();
                        previousPoint = null;
                    }
                });


            municipios_avg = result.municipio_avg;
            ticks_res_avg = result.ticks_avg;

            var plot = $.plot($("#chart_2"), [{
                            data: municipios_avg,
                            label: "Promedio Otorgado por Municipio"
                        }
                    ], {
                        series: {
                            lines: {
                                show: true,
                                lineWidth: 2,
                                fill: true,
                                fillColor: {
                                    colors: [{
                                            opacity: 0.05
                                        }, {
                                            opacity: 0.01
                                        }
                                    ]
                                }
                            },
                            points: {
                                show: true
                            },
                            shadowSize: 2
                        },
                        grid: {
                            hoverable: true,
                            clickable: true,
                            tickColor: "#eee",
                            borderWidth: 0
                        },
                        colors: ["#DB5E8C", "#F0AD4E", "#5E87B0"],
                        xaxis: {
                            ticks:  ticks_res_avg
                        },
                        yaxis: {
                            ticks: 11,
                            tickDecimals: 0
                        }
                    });

                var previousPoint = null;
                $("#chart_2").bind("plothover", function (event, pos, item) {
                    $("#x").text(pos.x.toFixed(2));
                    $("#y").text(pos.y.toFixed(2));

                    if (item) {
                        if (previousPoint != item.dataIndex) {
                            previousPoint = item.dataIndex;

                            $("#tooltip").remove();
                            var x = item.datapoint[0].toFixed(2),
                                y = item.datapoint[1].toFixed(2);

                            showTooltip(item.pageX, item.pageY, y);
                        }
                    } else {
                        $("#tooltip").remove();
                        previousPoint = null;
                    }
                });

            actividad_pend = result.actividad_pend;
            ticks_res_pend = result.ticks_pend;
            var plot = $.plot($("#chart_3"), [{
                            data: actividad_pend,
                            label: "Cantidad Otorgada por Actividad Económica"
                        }
                    ], {
                        series: {
                            lines: {
                                show: true,
                                lineWidth: 2,
                                fill: true,
                                fillColor: {
                                    colors: [{
                                            opacity: 0.05
                                        }, {
                                            opacity: 0.01
                                        }
                                    ]
                                }
                            },
                            points: {
                                show: true
                            },
                            shadowSize: 2
                        },
                        grid: {
                            hoverable: true,
                            clickable: true,
                            tickColor: "#eee",
                            borderWidth: 0
                        },
                        colors: ["#DB5E8C", "#F0AD4E", "#5E87B0"],
                        xaxis: {
                            ticks:  ticks_res_pend
                        },
                        yaxis: {
                            ticks: 11,
                            tickDecimals: 0
                        }
                    });

                var previousPoint = null;
                $("#chart_3").bind("plothover", function (event, pos, item) {
                    $("#x").text(pos.x.toFixed(2));
                    $("#y").text(pos.y.toFixed(2));

                    if (item) {
                        if (previousPoint != item.dataIndex) {
                            previousPoint = item.dataIndex;

                            $("#tooltip").remove();
                            var x = item.datapoint[0].toFixed(2),
                                y = item.datapoint[1].toFixed(2);

                            showTooltip(item.pageX, item.pageY, y);
                        }
                    } else {
                        $("#tooltip").remove();
                        previousPoint = null;
                    }
                });       
        }
    });
}

function chart1() {

    
}

function showTooltip(x, y, contents) {
    $('<div id="tooltip">' + contents + '</div>').css({
            position: 'absolute',
            display: 'none',
            top: y + 5,
            left: x + 15,
            border: '1px solid #333',
            padding: '4px',
            color: '#fff',
            'border-radius': '3px',
            'background-color': '#333',
            opacity: 0.80
        }).appendTo("body").fadeIn(200);
}