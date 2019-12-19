(function ($, Config) {

    // Update the table only when the tool is shown
    $(document).on('wpra/tools/on_loaded', function (event, tool) {
        if (tool === 'crons') {
            if (Store.isLoaded) {
                init();
            } else {
                $(document).ready(init);
            }
        }

        $(document).on('wpra/tools/on_switched_to_crons', init);
    });

    /**
     * Initializes the crons tool.
     */
    function init() {
        Pagination.init();
        Table.init();
        Timeline.init();
        Info.init();
    }

    /**
     * The data store.
     */
    var Store = {
        feeds: null,
        count: 0,
        isLoaded: false,
        page: 1,

        fetchSources: function (callback) {
            Pagination.showLoading();

            $.ajax({
                url: 'http://dev.wpra/wp-json/wpra/v1/sources',
                method: 'GET',
                data: {
                    num: Config.perPage,
                    page: Store.page
                },
                beforeSend: function (request) {
                    request.setRequestHeader("X-WP-NONCE", Config.restApiNonce);
                },
                success: function (response) {
                    if (response && response.items) {
                        Store.count = response.count;
                        Store.feeds = response.items.sort(function (a, b) {
                            return Util.compareTimeObjs(Feed.getUpdateTime(a), Feed.getUpdateTime(b));
                        });
                    }

                    Pagination.update();
                    Table.update();
                    Timeline.update();
                    Info.update();

                    if (callback) {
                        callback();
                    }
                },
                error: function (response) {
                    console.error(response);
                },
                complete: function () {
                    Pagination.hideLoading();
                }
            });
        },

        getIntervalName: function (interval) {
            return Config.schedules[interval]
                ? Config.schedules[interval]['display']
                : interval;
        },

        getGroupedFeeds: function () {
            var grouped = {};

            for (var i in Store.feeds) {
                var feed = Store.feeds[i];
                var time = Feed.getUpdateTime(feed),
                    timeStr = Util.formatTimeObj(time);

                if (!grouped[timeStr]) {
                    grouped[timeStr] = [];
                }

                grouped[timeStr].push(feed);
            }

            var collapsed = {};
            for (var timeStr in grouped) {
                // Get the time object and string for the previous minute
                var group = grouped[timeStr],
                    time = Util.parseTimeStr(timeStr),
                    prevTime = Util.addTime(time, {hours: 0, minutes: -1}),
                    prevTimeStr = Util.formatTimeObj(prevTime);

                // The key to use - either this group's time string or a time string for 1 minute less
                var key = grouped.hasOwnProperty(prevTimeStr)
                    ? prevTimeStr
                    : timeStr;

                // Create the array for the key if needed
                if (!Array.isArray(collapsed[key])) {
                    collapsed[key] = [];
                }

                // Add the group to the array for the key
                collapsed[key] = collapsed[key].concat(group);
            }

            return Object.keys(collapsed).sort().reduce((acc, key) => (acc[key] = collapsed[key], acc), {});
        },
    };

    /**
     * Functions related to feed sources and their data.
     */
    var Feed = {
        getState: function (feed) {
            return feed.active ? 'active' : 'paused';
        },
        getUpdateInterval: function (feed) {
            return (feed.update_interval === 'global')
                ? Config.globalWord
                : feed.update_interval;
        },
        getUpdateTime: function (feed) {
            return (feed.update_time)
                ? Util.parseTimeStr(feed.update_time)
                : Util.parseTimeStr(Config.globalTime);
        },
    };

    /**
     * The feed sources table.
     */
    var Table = {
        element: null,
        body: null,
        hovered: null,
        init: function () {
            if (Table.element === null) {
                Table.element = $('#wpra-crons-tool-table');
                Table.body = Table.element.find('tbody');
            }
            if (Store.feeds === null) {
                Store.fetchSources();
            }
        },
        createRow: function (feed) {
            var id = feed.id,
                state = Feed.getState(feed),
                name = feed.name,
                interval = Util.getIntervalName(Feed.getUpdateInterval(feed)),
                timeStr = Util.formatTimeObj(Feed.getUpdateTime(feed));

            var elRow = $('<tr></tr>').addClass('wpra-crons-feed-' + Feed.getState(feed));

            $('<td></td>').appendTo(elRow).addClass('wpra-crons-feed-id-col').text('#' + id);
            $('<td></td>').appendTo(elRow).addClass('wpra-crons-feed-name-col').text(name);
            $('<td></td>').appendTo(elRow).addClass('wpra-crons-interval-col').text(interval);
            $('<td></td>').appendTo(elRow).addClass('wpra-crons-time-col').text(timeStr ? timeStr : '');

            elRow.on('click', function (e) {
                Table.body.find('.wpra-crons-highlighted-feed').removeClass('wpra-crons-highlighted-feed');

                if (Table.hovered === id) {
                    Table.hovered = null;
                } else {
                    $(this).addClass('wpra-crons-highlighted-feed');
                    Table.hovered = id;
                }

                Timeline.update();
            });

            return elRow;
        },
        update: function () {
            Table.body.empty();

            for (var i in Store.feeds) {
                Table.body.append(Table.createRow(Store.feeds[i]));
            }
        },
    };

    /**
     * The pagination component.
     */
    var Pagination = {
        nextBtn: null,
        prevBtn: null,
        numFeeds: null,
        loadingText: null,
        // Initializes the pagination
        init: function () {
            Pagination.nextBtn = $('#wpra-crons-next-page');
            Pagination.prevBtn = $('#wpra-crons-prev-page');
            Pagination.numFeeds = $('.wpra-crons-num-feeds');
            Pagination.loadingText = $('#wpra-crons-loading');

            // Hide the feed counter until the component updates
            Pagination.numFeeds.parent().hide();

            Pagination.nextBtn.click(Pagination.nextPage);
            Pagination.prevBtn.click(Pagination.prevPage);
        },
        // Updates the pagination component
        update: function () {
            Pagination.nextBtn.prop('disabled', Store.page >= Pagination.getNumPages());
            Pagination.prevBtn.prop('disabled', Store.page <= 1);

            Pagination.numFeeds.text(Store.count);
            Pagination.numFeeds.parent().toggle(Store.count > 0);
        },
        // Calculates the number of pages
        getNumPages: function () {
            return Math.ceil(Store.count / Config.perPage);
        },
        // Switches to the next page
        nextPage: function () {
            Pagination.changePage(Math.min(Store.page + 1, Pagination.getNumPages()));
        },
        // Switches to the previous page
        prevPage: function () {
            Pagination.changePage(Math.max(Store.page - 1, 1));
        },
        // Switches to a specific page
        changePage: function (page) {
            Store.page = page;
            Store.fetchSources();
        },
        // Shows the loading text
        showLoading: function () {
            Pagination.loadingText.show();
        },
        // Hides the loading text
        hideLoading: function () {
            Pagination.loadingText.hide();
        },
    };

    /**
     * The info component.
     */
    var Info = {
        elGlobalInterval: null,
        elGlobalTime: null,
        elDownloadTimeline: null,
        init: function () {
            Info.elGlobalInterval = $('.wpra-crons-global-interval');
            Info.elGlobalTime = $('.wpra-crons-global-time');
            Info.elDownloadTimeline = $('.wpra-crons-download-timeline');

            Info.elDownloadTimeline.click(function () {
                var imageUrl = Timeline.canvas.toDataURL();
                window.open(imageUrl, '_wpraTimelineDL');
                window.focus();
            });
        },
        update: function () {
            Info.elGlobalInterval.text(Util.getIntervalName(Config.globalInterval));
            Info.elGlobalTime.text(Config.globalTime);
        }
    };

    /*
     * The timeline diagram.
     */
    var Timeline = {
        element: null,
        canvas: null,
        minWidth: 1280,

        init: function () {
            Timeline.element = document.getElementById('wpra-crons-timeline');
            Timeline.canvas = document.getElementById('wpra-crons-timeline-canvas');

            Timeline.update();
            window.addEventListener('resize', Timeline.update, false);
        },

        update: function () {
            // Update the width of the canvas to match its parent (-2 for the border of the parent)
            Timeline.canvas.width = Math.max(Timeline.minWidth, Timeline.element.offsetWidth - 2);

            // Get canvas properties
            var canvas = Timeline.canvas,
                rWidth = canvas.width,
                rHeight = canvas.height,
                hPadding = 10,
                vPadding = 10,
                width = rWidth - (hPadding * 2),
                height = rHeight - (vPadding * 2),
                ctx = canvas.getContext("2d"),
                axisOffset = 10,
                textHeight = 30,
                textSpacing = 20,
                lineY = height - textSpacing - textHeight,
                lineColor = "#555",
                hourGuideColor = "#888",
                minsGuideColor = "#aaa",
                lineWidth = 2,
                evenTextColor = "#444",
                oddTextColor = "#888",
                bubbleColor = "#658c6f",
                bubbleWarningColor = "#b18e76",
                bubbleSeriousColor = "#915759",
                bubbleHighlightColor = "#1a83de",
                bubbleRadius = 12,
                bubbleTopOffset = 5,
                bubbleTop = (bubbleRadius * 2) + bubbleTopOffset;

            // Clear the canvas
            ctx.clearRect(0, 0, width, height);
            ctx.translate(hPadding, vPadding);

            // Draw the bottom line
            {
                ctx.save();
                ctx.beginPath();
                ctx.moveTo(0, lineY);
                ctx.lineTo(width, lineY);
                ctx.lineWidth = lineWidth;
                ctx.strokeStyle = lineColor;
                ctx.stroke();
                ctx.restore();
            }

            // Pad along the x-axis so that the numbers are not exactly at the edges
            ctx.translate(axisOffset, 0);

            // Draw the numbers and dotted lines
            {
                var hourWidth = width / 24,
                    minFontSize = 12,
                    maxFontSize = 18,
                    fontSizeRatio = 0.011,
                    fontSize = Math.max(Math.min(width * fontSizeRatio, maxFontSize), minFontSize);

                ctx.font = fontSize + "px sans-serif";
                ctx.textBaseline = "hanging";
                for (var hour = 0; hour < 24; ++hour) {
                    var hourStr = (hour < 10) ? "0" + hour : hour,
                        text = hourStr + ":00",
                        even = (hour % 2 === 0),
                        x = hour * hourWidth,
                        y = height - textHeight - (textSpacing / 2),
                        tx = x,
                        ty = y + 3,
                        color = (even) ? evenTextColor : oddTextColor;

                    ctx.save();

                    ctx.translate(tx, ty);
                    ctx.rotate(Math.PI / 5);
                    ctx.fillStyle = color;
                    ctx.textAlign = "left";
                    ctx.fillText(text, 0, 0);

                    ctx.restore();

                    // The hour guide lines
                    ctx.save();
                    ctx.beginPath();
                    ctx.moveTo(x, y);
                    ctx.lineTo(x, 0);
                    ctx.setLineDash([4, 4]);
                    ctx.lineWidth = 1;
                    ctx.strokeStyle = hourGuideColor;
                    ctx.stroke();
                    ctx.moveTo(0, 0);
                    ctx.restore();

                    // The half-hour guide lines
                    ctx.save();
                    ctx.beginPath();
                    ctx.moveTo(x + (hourWidth / 2), y);
                    ctx.lineTo(x + (hourWidth / 2), 0);
                    ctx.setLineDash([2, 2]);
                    ctx.lineWidth = 1;
                    ctx.strokeStyle = minsGuideColor;
                    ctx.stroke();
                    ctx.moveTo(0, 0);
                    ctx.restore();
                }
            }

            // Draw the indicators
            {
                if (Store.feeds && Store.feeds.length) {
                    var minuteWidth = width / (24 * 60),
                        fetchDuration = 5, // in minutes
                        fetchWidth = fetchDuration * minuteWidth;

                    var groups = Store.getGroupedFeeds();
                    var drawLater = {};

                    var drawFn = function (group, timeStr, highlighted) {
                        var time = Util.parseTimeStr(timeStr),
                            groupX = (time.hours * hourWidth) + (time.minutes / 60 * hourWidth),
                            count = group.length,
                            color = bubbleColor,
                            bgColor = "#fff",
                            textColor = color;

                        if (highlighted) {
                            color = bubbleHighlightColor;
                            bgColor = color;
                            textColor = "#fff";
                        } else if (count > 10) {
                            color = bubbleSeriousColor;
                        } else if (count > 5) {
                            color = bubbleWarningColor;
                        }

                        // Draw the indicator line
                        ctx.save();
                        ctx.beginPath();
                        ctx.moveTo(groupX, lineY);
                        ctx.lineTo(groupX, bubbleTop);
                        ctx.lineCap = "square";
                        ctx.lineWidth = 2;
                        ctx.strokeStyle = color;
                        ctx.stroke();
                        ctx.restore();

                        // Draw the bubble
                        ctx.save();
                        ctx.beginPath();
                        ctx.arc(groupX, bubbleRadius + bubbleTopOffset, bubbleRadius, 0, 2 * Math.PI);
                        ctx.fillStyle = bgColor;
                        ctx.fill();
                        ctx.lineWidth = 2;
                        ctx.strokeStyle = color;
                        ctx.stroke();
                        ctx.restore();

                        // Draw the feed count
                        ctx.save();
                        ctx.font = "12px sans-serif";
                        ctx.textAlign = "center";
                        ctx.textBaseline = "middle";
                        ctx.fillStyle = textColor;
                        ctx.fillText(count, groupX, bubbleRadius + bubbleTopOffset + 1);
                        ctx.restore();
                    };

                    for (var timeStr in groups) {
                        var group = groups[timeStr];

                        var hasHighlightedFeed = Table.hovered !== null && group.find(function (feed) {
                            return feed.id === Table.hovered;
                        });

                        if (hasHighlightedFeed) {
                            drawLater[timeStr] = group;
                        }

                        drawFn(group, timeStr);
                    }

                    for (var timeStr in drawLater) {
                        drawFn(drawLater[timeStr], timeStr, true);
                    }
                }
            }

            ctx.translate(0, 0);
        },

        drawLollipop: function () {

        },
    };

    /**
     * Utility functions.
     */
    var Util = {
        getIntervalName: function (interval) {
            return Config.schedules[interval]
                ? Config.schedules[interval]['display']
                : interval;
        },
        formatTimeObj: function (obj) {
            if (!obj) {
                return "";
            }

            var hours = obj.hours < 10 ? "0" + obj.hours : obj.hours;
            var minutes = obj.minutes < 10 ? "0" + obj.minutes : obj.minutes;

            return hours + ":" + minutes;
        },
        parseTimeStr: function (timeStr) {
            var parts = timeStr.split(':');
            var hours = parseInt(parts[0]);
            var mins = parseInt(parts[1]);

            return {hours: hours, minutes: mins};
        },
        addTime: function (obj1, obj2) {
            var newObj = {
                hours: obj1.hours + obj2.hours,
                minutes: obj1.minutes + obj2.minutes
            };

            newObj.hours = (newObj.hours + Math.floor(newObj.minutes / 60)) % 23;
            newObj.minutes = newObj.minutes % 60;

            return newObj;
        },
        compareTimeObjs(a, b) {
            var an = (a.hours * 60) + a.minutes;
            var bn = (b.hours * 60) + b.minutes;

            if (an === bn) {
                return 0;
            }

            return (an < bn) ? -1 : 1;
        }
    };

})(jQuery, WpraCronsTool);
