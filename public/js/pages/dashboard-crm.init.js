function getChartColorsArray(chartId) {
    var chart = document.getElementById(chartId);
    if (!chart) return null;

    var colors = chart.getAttribute("data-colors");
    if (!colors) {
        console.warn("data-colors Attribute not found on:", chartId);
        return null;
    }

    return JSON.parse(colors).map(function (value) {
        var color = value.replace(" ", "");
        if (color.indexOf(",") === -1) {
            var cssColor = getComputedStyle(document.documentElement).getPropertyValue(color);
            return cssColor || color;
        }

        var rgba = color.split(",");
        if (rgba.length !== 2) return color;

        return "rgba(" + getComputedStyle(document.documentElement).getPropertyValue(rgba[0]) + "," + rgba[1] + ")";
    });
}

function normalizeNumericArray(values, expectedLength) {
    var source = Array.isArray(values) ? values : [];
    var normalized = source.map(function (value) {
        var numeric = Number(value);
        return Number.isFinite(numeric) ? numeric : 0;
    });

    if (typeof expectedLength === "number" && expectedLength >= 0) {
        normalized = normalized.slice(0, expectedLength);
        while (normalized.length < expectedLength) {
            normalized.push(0);
        }
    }

    return normalized;
}

function getDashboardData(path, fallbackValue) {
    var source = window.crmDashboardData || {};
    var current = source;

    for (var i = 0; i < path.length; i++) {
        if (current == null || typeof current !== "object" || !(path[i] in current)) {
            return fallbackValue;
        }

        current = current[path[i]];
    }

    return current;
}

(function () {
    var salesColors = getChartColorsArray("sales-forecast-chart");
    var salesChartEl = document.querySelector("#sales-forecast-chart");

    if (salesColors && salesChartEl) {
        var ventasSeries = normalizeNumericArray(getDashboardData(["ventasProductos", "series"], []), 3);
        var agenciasSeries = normalizeNumericArray(getDashboardData(["ventasProductos", "agencias"], []), 3);
        var isMobile = window.matchMedia("(max-width: 768px)").matches;

        if (!ventasSeries.some(function (value) { return value > 0; })) {
            try {
                ventasSeries = normalizeNumericArray(JSON.parse(salesChartEl.getAttribute("data-series") || "[]"), 3);
            } catch (e) {
                ventasSeries = [0, 0, 0];
            }
        }

        if (!agenciasSeries.some(function (value) { return value > 0; })) {
            try {
                agenciasSeries = normalizeNumericArray(JSON.parse(salesChartEl.getAttribute("data-agencias") || "[]"), 3);
            } catch (e) {
                agenciasSeries = [0, 0, 0];
            }
        }

        var formatAgenciasLabel = function (value) {
            var numeric = Number(value || 0);
            if (numeric >= 1000) {
                return (numeric / 1000).toFixed(1).replace(/\.0$/, "") + "k";
            }

            return String(numeric);
        };

        var salesOptions = {
            series: [
                { name: "Tradicional", data: [Number(ventasSeries[0] || 0)] },
                { name: "No Tradicional", data: [Number(ventasSeries[1] || 0)] },
                { name: "Recargas", data: [Number(ventasSeries[2] || 0)] }
            ],
            chart: {
                type: "bar",
                height: 360,
                toolbar: { show: false }
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: "64%",
                    dataLabels: {
                        position: "center"
                    }
                }
            },
            dataLabels: {
                enabled: true,
                formatter: function (_val, opts) {
                    return formatAgenciasLabel(agenciasSeries[opts.seriesIndex] || 0);
                },
                style: {
                    fontSize: isMobile ? "12px" : "15px",
                    fontWeight: "700",
                    colors: ["#ffffff"]
                },
                offsetY: -2,
                dropShadow: {
                    enabled: true,
                    blur: 0,
                    opacity: 0.2
                }
            },
            stroke: {
                show: true,
                width: 4,
                colors: ["transparent"]
            },
            xaxis: {
                categories: [""],
                axisTicks: {
                    show: false,
                    borderType: "solid",
                    color: "#78909C",
                    height: 6,
                    offsetX: 0,
                    offsetY: 0
                }
            },
            yaxis: {
                labels: {
                    formatter: function (value) {
                        return "RD$ " + Number(value).toLocaleString("es-DO", {
                            minimumFractionDigits: 0,
                            maximumFractionDigits: 0
                        });
                    }
                },
                tickAmount: 4,
                min: 0
            },
            fill: { opacity: 1 },
            legend: {
                show: true,
                position: "bottom",
                horizontalAlign: "center",
                fontWeight: 500,
                offsetX: 0,
                offsetY: -12,
                itemMargin: { horizontal: 8, vertical: 0 },
                markers: { width: 10, height: 10 }
            },
            tooltip: {
                y: {
                    formatter: function (value, opts) {
                        var agencias = agenciasSeries[opts.seriesIndex] || 0;
                        var monto = Number(value || 0).toLocaleString("es-DO", {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });

                        return "RD$ " + monto + " | Agencias: " + agencias;
                    }
                }
            },
            colors: salesColors
        };

        new ApexCharts(salesChartEl, salesOptions).render();
    }

    var dealTypeChartsColors = getChartColorsArray("deal-type-charts");
    if (dealTypeChartsColors) {
        var dealTypeOptions = {
            series: [
                { name: "Pending", data: [80, 50, 30, 40, 100, 20] },
                { name: "Loss", data: [20, 30, 40, 80, 20, 80] },
                { name: "Won", data: [44, 76, 78, 13, 43, 10] }
            ],
            chart: {
                height: 341,
                type: "radar",
                dropShadow: { enabled: true, blur: 1, left: 1, top: 1 },
                toolbar: { show: false }
            },
            stroke: { width: 2 },
            fill: { opacity: 0.2 },
            legend: {
                show: true,
                fontWeight: 500,
                offsetX: 0,
                offsetY: -8,
                markers: { width: 8, height: 8, radius: 6 },
                itemMargin: { horizontal: 10, vertical: 0 }
            },
            markers: { size: 0 },
            colors: dealTypeChartsColors,
            xaxis: { categories: ["2016", "2017", "2018", "2019", "2020", "2021"] }
        };

        new ApexCharts(document.querySelector("#deal-type-charts"), dealTypeOptions).render();
    }

    var revenueExpensesChartsColors = getChartColorsArray("revenue-expenses-charts");
    if (revenueExpensesChartsColors) {
        var revenueExpensesEl = document.querySelector("#revenue-expenses-charts");
        var revenueCategories = getDashboardData(["resumenBalance", "categories"], []);
        var ingresosData = getDashboardData(["resumenBalance", "ingresos"], []);
        var gastosData = getDashboardData(["resumenBalance", "gastos"], []);
        var margenData = getDashboardData(["resumenBalance", "margen"], []);

        if ((!Array.isArray(revenueCategories) || !revenueCategories.length) && revenueExpensesEl) {
            try {
                revenueCategories = JSON.parse(revenueExpensesEl.getAttribute("data-categories") || "[]");
                ingresosData = JSON.parse(revenueExpensesEl.getAttribute("data-ingresos") || "[]");
                gastosData = JSON.parse(revenueExpensesEl.getAttribute("data-gastos") || "[]");
                margenData = JSON.parse(revenueExpensesEl.getAttribute("data-margen") || "[]");
            } catch (_e) {
                revenueCategories = [];
                ingresosData = [];
                gastosData = [];
                margenData = [];
            }
        }

        if (!Array.isArray(revenueCategories) || !revenueCategories.length) {
            revenueCategories = ["01"];
        }

        var daysCount = revenueCategories.length;
        var fillToDays = function (arr) {
            var result = normalizeNumericArray(arr, daysCount);
            while (result.length < daysCount) result.push(0);
            return result;
        };

        ingresosData = fillToDays(ingresosData);
        gastosData = fillToDays(gastosData);
        margenData = fillToDays(margenData);

        var revenueExpensesOptions = {
            series: [
                { name: "Tradicional", data: ingresosData },
                { name: "No Tradicional", data: gastosData },
                { name: "Recargas", data: margenData }
            ],
            chart: {
                height: 290,
                type: "area",
                toolbar: false
            },
            dataLabels: { enabled: false },
            stroke: { curve: "smooth", width: 2 },
            xaxis: { categories: revenueCategories },
            yaxis: {
                labels: {
                    formatter: function (value) {
                        return "RD$ " + Number(value || 0).toLocaleString("es-DO", {
                            minimumFractionDigits: 0,
                            maximumFractionDigits: 0
                        });
                    }
                },
                tickAmount: 5,
                min: 0
            },
            tooltip: {
                y: {
                    formatter: function (value) {
                        return "RD$ " + Number(value || 0).toLocaleString("es-DO", {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                    }
                }
            },
            colors: revenueExpensesChartsColors,
            fill: {
                opacity: 0.06,
                colors: revenueExpensesChartsColors,
                type: "solid"
            }
        };

        new ApexCharts(revenueExpensesEl, revenueExpensesOptions).render();
    }
})();
