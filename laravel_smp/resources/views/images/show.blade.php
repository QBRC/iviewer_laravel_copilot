@extends('layouts.app')

@section('style')
    <link href="{{ asset('css/annotorious2.7.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/viewer.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/jstree/themes/default/style.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <!-- <link rel="stylesheet" href="http://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css"> -->
    <link rel="stylesheet" href="{{ asset('vendor/jquery-ui-1.12.1.custom/jquery-ui.min.css') }}">


@endsection

@section('content')
    <div class="container-fluid">
        <nav aria-label="breadcrumb" id="image_desc">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">{{ $image->batch->project }}</li>
{{--                <li class="breadcrumb-item"><a href="{{ url("slides/".$image->batch->id) }}">{{ $image->batch->name }}</a></li>--}}
                <li class="breadcrumb-item">{{ $image->batch->name }}</li>
                <li class="breadcrumb-item active" aria-current="page">{{ $image->name }}</li>
            </ol>
        </nav>

        @if($status)
        <!-- <div class="row">
            <div class="last-anno my-2 col-md-6 col-sm-6 col-6"></div>
            <small class="text-right my-2 col-md-6 col-sm-6 col-6">&divideontimes;Clicking hide/show annotation button
                will discard your unsaved annotation</small>
        </div> -->

        <!-- Annotation Toolbar -->
        <div class="row" id="toolbar">
            <div id="manual-toolbar" class="inner"></div>
            <div class="vertical-line"></div>
            <div class="model-btn-grp">
                @foreach($modelBtn as $k=>$v)
                    <button class="btn btn-sm btn-secondary" id="{{ $v['id'] }}">
                    @if($v['text'] == 'HDYolo')
                        <i class="fa fa-cog model-gear"></i>
                    @endif
                         {{ $v['text'] }}</button>
                @endforeach
            </div>
            <div class="vertical-line"></div>
            <div class="viewer-btn">
                <button class="btn btn-outline-primary btn-sm d-inline-block mr-2 mb-2" onclick="toggleSingleDual()">
                    <i class="far fa-images"></i> Toggle single/dual-view mode
                </button>
                <button class="btn btn-outline-primary btn-sm d-inline-block mr-2 toggle-full mb-2"
                        onclick="fullScreen()">
                    <i class="fas fa-expand-arrows-alt"></i> Toggle fullscreen
                </button>
            </div>

        </div>

        <!-- Viewer Btn Group -->
        <!-- <div class="row" id="viewer-btn">
            <div class="col-md-12 col-md-12 col-sm-12 col-12">
                <button class="btn btn-outline-primary btn-sm d-inline-block mr-2 mb-2" onclick="toggleSingleDual()">
                    <i class="far fa-images"></i> Toggle single/dual-view mode
                </button>
                <button class="btn btn-outline-primary btn-sm d-inline-block mr-2 mb-2" onclick="hideOrShowAnno()">
                    <i class="far fa-eye"></i> Hide/Show annotation
                </button>
                <button class="btn btn-outline-primary btn-sm d-inline-block mr-2 toggle-full mb-2"
                        onclick="fullScreen()">
                    <i class="fas fa-expand-arrows-alt"></i> Toggle fullscreen
                </button>
                <a href="" data-toggle="modal" data-target="#saveModal"
                   class="btn btn-outline-primary btn-sm d-inline-block mr-2 mb-2">
                    <i class="fas fa-save"></i> Save annotation
                </a>
            </div>
        </div> -->
        <!-- Mouse Track Switch -->
        <div style="display: flex;">
            <label class="switch" id="sliderSwitch" style="margin-bottom: 0">
                <input type="checkbox" id="showMouseTrack">
                <span class="slider round"></span>
            </label>
            <small id="sliderTooltip" class="mousetrack"></small>
        </div>
        <!-- Mouse Track Display -->
        <div class="w-100">
            <div id="leftmousetrack" class="viewertooltip float-left d-inline-block w-100"></div>
            <div id="rightmousetrack" class="viewertooltip float-right d-none w-50"></div>
        </div>
        <!-- Dual Viewer -->
        <div id="viewer" class="w-100" style="height: 700px">
            <div id="leftViewer" class="border border-secondary d-inline-block float-left m-0 w-100 h-100"></div>
            <div id="rightViewer" class="border border-secondary d-none float-right m-0 w-50 h-100"></div>
        </div>
        {{-- Legend --}}
        <!-- <div class="mt-2 h6">
            <div class="legend-box" style="background-color: #23FF08"></div>
            Tumor Nuclei
            <div class="legend-box" style="background-color: #0E00FC"></div>
            Lymphocyte Nuclei
            <div class="legend-box" style="background-color: #F8000A"></div>
            Stroma Nuclei
            <div class="legend-box" style="background-color: #FEFF0F"></div>
            Macrophage Nuclei
            <div class="legend-box" style="background-color: #1A95D8"></div>
            Karyorrhexis
            <div class="legend-box" style="background-color: #F900FF"></div>
            Red Blood Cells
        </div> -->

        {{-- Annotation history --}}
        <div class="card shadow my-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Annotation history</h6>
            </div>
            <div class="card-body">
                <div class="alert alert-danger" role="alert">
                    Please select at least 1 annotation record.
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="dataTable" width="100%" cellspacing="0">
                    </table>
                </div>
            </div>

        </div>
        @else
            <p>This image does not exist</p>
        @endif
    </div>

    <!-- Save Annotation Modal -->
    <div class="modal fade" id="saveModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Save current annotation</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>

                <div class="form-group row mt-3">
                    <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('Name') }}</label>

                    <div class="col-md-6">
                        <input id="name" type="text" class="form-control" name="name" value=""
                               placeholder="Annotation name" required autocomplete="name" autofocus>

                        <span class="invalid-feedback" role="alert"></span>
                    </div>
                </div>

                <div class="modal-body">

                </div>
                <div class="modal-footer">
                    <button class="btn btn-outline-secondary mr-3" type="button" data-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary save-annotation">Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- floating window for Left Layer-->
    <div id="floatingWindow-left" title="Annotators (Left Viewer)" >
        <div class="alert alert-warning zoomin-message-left hidden" role="alert">Zoom in on the image for viewing HDYolo annotations</div>
        <div class="layers" id="layers-left"></div>
    </div>
    <div id="floatingWindow-right" title="Annotator (Right Viewer)" >
        <div class="alert alert-warning zoomin-message-right hidden" role="alert">Zoom in on the image for viewing HDYolo annotations</div>
        <div class="layers" id="layers-right"></div>
    </div>


@endsection

@section('scripts')
    <!-- {{--    <script type="text/javascript" src="{{ asset('vendor/viewer/openseadragon242.min.js') }}"></script>--}}
    {{--    <script type="text/javascript" src="{{ asset('vendor/viewer/openseadragon-annotorious.min.js') }}"></script>--}} -->
    <!-- DataTables CSS -->
    <!-- <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css"> -->

    <!-- DataTables Buttons CSS -->
    <!-- <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.1.1/css/buttons.dataTables.min.css"> -->

    <!-- DataTables JS -->
    <!-- <script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script> -->

    <!-- DataTables Buttons JS -->
    <!-- <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.1.1/js/dataTables.buttons.min.js"></script>  -->
    <!-- <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.1.1/js/buttons.html5.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.1.1/js/buttons.print.min.js"></script>  -->

    <!-- <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script> -->
    <script src="{{ asset('vendor/jquery-ui-1.12.1.custom/jquery-ui.js') }}"></script>
    <script type="text/javascript" src="{{ asset('vendor/viewer/openseadragon4.0.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('vendor/viewer/openseadragon-annotorious2.7.min.js') }}"></script>

    <script type="text/javascript" src="{{ asset('vendor/viewer/openseadragon-scalebar.js') }}"></script>
    <!-- <script type="text/javascript" src="{{ asset('vendor/viewer/annotorious.min.js') }}"></script> -->
    <script type="text/javascript" src="{{ asset('vendor/viewer/axios.min.js') }}"></script>

    <script type="text/javascript" src="{{ asset('vendor/viewer/annotorious-selector-pack.js') }}"></script>
    <script type="text/javascript" src="{{ asset('vendor/viewer/annotorious-better-polygon.js') }}"></script>
    <script type="text/javascript" src="{{ asset('vendor/viewer/annotorious-toolbar.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/utils.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/iviewer-utils.js') }}"></script>
    <script type="text/javascript" src="{{asset('js/annotorious-editor.js')}}"></script>

    <script type="text/javascript" src="{{ asset('js/jszip.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/fileSaver.js') }}"></script>
    <!-- <script type="text/javascript" src="{{ asset('vendor/jquery/jquery.min.js') }}"></script> -->
    <script type="text/javascript" src="{{ asset('vendor/jstree/jstree.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/konva.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/openseadragon-iviewer-annotation.js') }}"></script>

    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        var data =@json($data);
        var image =@json($image);
        var folders =@json(config('app.iv.folder'));

        var imgid = "{{ $image->uuid }}";
        var imgname = "{{ $image->name }}";
        var width = "{{ $image->width }}";
        var hasmask = "{{$mask}}";
        var userId = "{{ Auth::user()->id }}";
        var userName = "{{ Auth::user()->name }}";
        const modelBtnData = @json($modelBtn);
        const chatAPI = data['original']['chat'];

        var originalTileImg = data['original']['tile'], maskTileImg = data['mask']['tile'];
        var height = "{{ $image->height }}";
        // var historyColumns = [
        //     {"title": "<input type='checkbox' id='check_all style='display: block !important;'>"},
        //     {"title": "Description"},
        //     {"title": "Creator"},
        //     {"title": "Date"},
        //     {"title": "View"}
        // ];

        /**
         * ----- Fetch mpp value from API ------
         */
        var paramsURL = data['original']['params']
        var mpp;
        var ppm;
        var magnitude;
        $.ajax({
            type: "GET",
            dataType: "json",
            url: paramsURL,
            async: false,
            success: function (data) {
                // if (data['slide_mpp']) {
                    mpp = data['slide_mpp'] ? data['slide_mpp'] : 0.125;
                    ppm = mpp ? (1e6 / mpp) : 0
                    magnitude = Math.round(10 / data['slide_mpp']);
                // }
                // else {
                //     mpp = ppm = magnitude = null;
                // }
            }
        });
        let max_zoomRation = mpp ? mpp / 0.125 * 1.1 : 1.1;

        /**
         * Initiate a viewer
         **/
        var leftViewer = OpenSeadragon({
            id: "leftViewer",
            prefixUrl: "{{ asset('img/viewer/') }}/",
            preserveViewport: true,
            tileSources: originalTileImg,
            maxZoomPixelRatio: max_zoomRation,
            preserveImageSizeOnResize: true
        });

        var rightViewer = OpenSeadragon({
            id: "rightViewer",
            prefixUrl: "{{ asset('img/viewer/') }}/",
            preserveViewport: true,
            tileSources: originalTileImg,
            maxZoomPixelRatio: max_zoomRation,
        });
    
        leftViewer.scalebar({
            type: OpenSeadragon.ScalebarType.MAP,
            pixelsPerMeter: ppm,
            minWidth: "75px",
            location: OpenSeadragon.ScalebarLocation.BOTTOM_LEFT,
            xOffset: 5,
            yOffset: 20,
            stayInsideImage: false,
            color: "rgb(150, 150, 150)",
            fontColor: "rgb(100, 100, 100)",
            backgroundColor: "rgba(255, 255, 255, 0.5)",
            fontSize: "small",
            barThickness: 2
        });

        //----------------zoom and pan in both viewers at same time----------------
        var leftViewerLeading = false;
        var rightViewerLeading = false;
        var viewportzoom;
        var imagezoom;
        var mppzoom;
        var mag;

        $('.zoom-value span').html(Math.round(viewportzoom) + 'x');
        var leftViewerHandler = function () {
            if (rightViewerLeading) {
                return;
            }
            viewPortZoom = leftViewer.viewport.getZoom();
            leftViewerLeading = true;
            rightViewer.viewport.zoomTo(viewPortZoom); //listened by handler
            rightViewer.viewport.panTo(leftViewer.viewport.getCenter());
            leftViewerLeading = false;

            imagezoom = leftViewer.viewport.viewportToImageZoom(viewPortZoom);
            mppzoom = (mpp ? mpp : 0.125) / imagezoom;
            mag = 10 / mppzoom;
            if (mag < 1) {
                $('.zoom-value span').html('<1x');
            } else {
                $('.zoom-value span').html(Math.round(mag) + 'x');
            }
            $('#zoom-slider').val(mag);
        };

        var rightViewerHandler = function () {
            if (leftViewerLeading) {
                return;
            }
            viewPortZoom = rightViewer.viewport.getZoom();
            rightViewerLeading = true;
            leftViewer.viewport.zoomTo(viewPortZoom);
            leftViewer.viewport.panTo(rightViewer.viewport.getCenter());
            rightViewerLeading = false;

            imagezoom = leftViewer.viewport.viewportToImageZoom(viewPortZoom);
            mppzoom = (mpp ? mpp : 0.125) / imagezoom;
            mag = 10 / mppzoom;
            if (mag < 1) {
                $('.zoom-value span').html('<1x');
            } else {
                $('.zoom-value span').html(Math.round(mag) + 'x');
            }
            $('#zoom-slider').val(mag);
        };

        leftViewer.addHandler('zoom', leftViewerHandler);
        rightViewer.addHandler('zoom', rightViewerHandler);
        leftViewer.addHandler('pan', leftViewerHandler);
        rightViewer.addHandler('pan', rightViewerHandler);

        // hide and show button
        let annotatorTreeBtn_left= new OpenSeadragon.Button({
            tooltip: 'Hide/Show Annotations',
            id: "left_switch",
            srcRest: "{{ asset('img/viewer/mask_rest.png') }}",
            srcGroup: "{{ asset('img/viewer/mask_grouphover.png') }}",
            srcHover: "{{ asset('img/viewer/mask_hover.png') }}",
            srcDown: "{{ asset('img/viewer/mask_pressed.png') }}",
        });
        let annotatorTreeBtn_right = new OpenSeadragon.Button({
            tooltip: 'Hide/Show Annotations',
            id: "right_switch",
            srcRest: "{{ asset('img/viewer/mask_rest.png') }}",
            srcGroup: "{{ asset('img/viewer/mask_grouphover.png') }}",
            srcHover: "{{ asset('img/viewer/mask_hover.png') }}",
            srcDown: "{{ asset('img/viewer/mask_pressed.png') }}",
        });
        let rightRefreshBtn = new OpenSeadragon.Button({
            tooltip: 'Refresh',
            id: "right_refresh",
            srcRest: "{{ asset('img/viewer/sync_rest.png') }}",
            srcGroup: "{{ asset('img/viewer/sync_grouphover.png') }}",
            srcHover: "{{ asset('img/viewer/sync_hover.png') }}",
            srcDown: "{{ asset('img/viewer/sync_pressed.png') }}",
        });
        var offLeft = true, onRight = true;
        // annotatorTreeBtn_left.addHandler('click', function (event) {
        //     $('#myModal').modal('show');
        // });
        // annotatorTreeBtn_right.addHandler('click', function (event){
        //     $('#rightModal').modal('show');
        // });
        rightRefreshBtn.addHandler('click', function(event) {
            annotationLayer_right.removeAllShapes();
            annotationLayer_right.renderActive();
            annotationLayer_right.draw();
        })
        function toggleSingleDual() {
            $("#leftViewer").toggleClass('w-100 w-50');
            $("#rightViewer").toggleClass('d-none d-inline-block');
            $("#leftmousetrack").toggleClass('w-100 w-50');
            $("#rightmousetrack").toggleClass('d-none d-inline-block');
            if ($("#floatingWindow-right").hasClass("ui-dialog-content")) {
                $("#floatingWindow-right").dialog("close");
            }
        }


        leftViewer.addControl(annotatorTreeBtn_left.element, {anchor: OpenSeadragon.ControlAnchor.TOP_LEFT});
        rightViewer.addControl(annotatorTreeBtn_right.element, {anchor: OpenSeadragon.ControlAnchor.TOP_LEFT});
        rightViewer.addControl(rightRefreshBtn.element, {anchor: OpenSeadragon.ControlAnchor.TOP_LEFT});


        /**
         * ----- Zoom slider panel configuration Start ------
         */
        if (mpp) {
            var zoom_btns = '';
            zoom_levels = [1, 5, 10, 20, 40];
            for (let i of zoom_levels) {
                zoom_btns += '<li value="' + i + '">' + i + 'x</li>';
            }

            $('#leftViewer .openseadragon-container').append('<div class="zoom-control" style="display: none;"><div class="d-flex">' +
                '<div><ul>' + zoom_btns + '</ul></div>' +
                '<div class="range-wrapper"><input orient="vertical" type="range" class="form-range" min="1" max="40" id="zoom-slider"></div></div>' +
                '<div class="zoom-value"><span>1x</span></div></div>');

            $('.form-range').on('input', function () {
                leftViewer.viewport.zoomTo(leftViewer.viewport.imageToViewportZoom(this.value * mpp / 10));
            });

            $('.zoom-control').on('click', 'li', function () {
                $('.form-range').val(this.value);
                leftViewer.viewport.zoomTo(leftViewer.viewport.imageToViewportZoom(this.value * mpp / 10));
            })

            let zoomSliderBtn = new OpenSeadragon.Button({
                tooltip: 'Toggle zoom slider',
                srcRest: "{{ asset('img/viewer/zoomControl_rest.png') }}",
                srcGroup: "{{ asset('img/viewer/zoomControl_grouphover.png') }}",
                srcHover: "{{ asset('img/viewer/zoomControl_hover.png') }}",
                srcDown: "{{ asset('img/viewer/zoomControl_pressed.png') }}",
                onClick: toggleSlider,
                class: "zoomControl-icon"
            });

            leftViewer.addControl(zoomSliderBtn.element, {
                anchor: OpenSeadragon.ControlAnchor.TOP_LEFT
            });

            function toggleSlider() {
                $('.zoom-control').toggle();
            }
        } 
        // else {
            // $('#leftViewer .openseadragon-container').append('<div class="mt-5 ml-2 position-relative" style="z-index: 999">' +
            //     '<i class="fas fa-info-circle mr-2"></i>slide mpp value not found.</div>');
        // }
        /**
         * ----- Zoom slider panel configuration End ------
         */


        //mouse Tracking
        const showMouseTrackSwitch = document.getElementById("showMouseTrack");

        function setupMouseTracker(viewer, mousetrackElementLeft, mousetrackElementRight) {
            var tracker;
            showMouseTrackSwitch.addEventListener("change", function () {
                if (showMouseTrackSwitch.checked) {
                    tracker = new OpenSeadragon.MouseTracker({
                        element: viewer.container,
                        moveHandler: function (event) {
                            var webPoint = event.position;
                            var viewportPoint = viewer.viewport.pointFromPixel(webPoint);
                            var imagePoint = viewer.viewport.viewportToImageCoordinates(viewportPoint);
                            mousetrackElementLeft.textContent = "X:" + imagePoint.x.toFixed(2) + " Y: " + imagePoint.y.toFixed(2);
                            mousetrackElementLeft.style.display = "block";
                            mousetrackElementRight.textContent = "X:" + imagePoint.x.toFixed(2) + " Y: " + imagePoint.y.toFixed(2);
                            mousetrackElementRight.style.display = "block";
                        }
                    });
                    tracker.setTracking(true);
                } else {
                    if (typeof tracker !== 'undefined') {
                        tracker.setTracking(false);
                        tracker.destroy();
                        tracker = null;
                    }

                    // Hide the mousetrack
                    mousetrackElementLeft.textContent = "";
                    mousetrackElementRight.textContent = "";
                }
            });
        }

        // Set up mouse trackers for both viewers
        const leftmt = document.getElementById("leftmousetrack"), rightmt = document.getElementById("rightmousetrack");
        setupMouseTracker(leftViewer, leftmt, rightmt);
        setupMouseTracker(rightViewer, leftmt, rightmt);

        // Add event listeners to show/hide tooltip on mouseover/mouseout of the slider switch
        const sliderSwitch = document.getElementById("sliderSwitch");
        const sliderTooltip = document.getElementById("sliderTooltip");
        sliderSwitch.addEventListener("mouseover", function () {
            sliderTooltip.textContent = "This is a Mouse Tracking Toggle";
            sliderTooltip.style.display = "block";
        });

        sliderSwitch.addEventListener("mouseout", function () {
            sliderTooltip.style.display = "none";
        });
    </script>

<!-- Initiate annotation layer  -->
<script>
    const colorPalette = new ColorPalette({
            'bg': "#ffffff",
            'tumor_nuclei': "#00ff00",
            'stromal_nuclei': "#ff0000",
            'immune_nuclei': "#0000ff",
            'blood_cell': "#ff00ff",
            'macrophage': "#ffff00",
            'dead_nuclei': "#0094e1",
            'other_nuclei': "#b581fe"
            
    });
    var annotationLayer_left = new IViewerAnnotation(leftViewer, {
        'layers': [{'capacity': 2048}],
        'widgets': [
            'COMMENT',
            {
                widget: 'TAG',
                vocabulary: colorPalette.labels(),
            },
            // aiAssistantWidget, 
            aiChatBox,
            AnnotatorWidget,
        ],
        'drawingTools': {
            tools: ['rect', 'polygon', 'circle', 'ellipse', 'freehand'],
            container: document.getElementById('manual-toolbar'),
        },
    });
    annotationLayer_left.colorPalette = colorPalette;  // Replace colorPalette with global one
    var annoCreateAPI = "{!! $annoAPI['createDB'] !!}";
    var annoGetAnnotatorsAPI = "{!! $annoAPI['getAnnotator'] !!}";
    var annoGetLabelsAPI =  "{!! $annoAPI['getLabels'] !!}";
    var annoInsertAPI = "{!! $annoAPI['insert'] !!}";
    var annoReadAPI = "{!! $annoAPI['read'] !!}";
    var annoUpdateAPI = "{!! $annoAPI['update'] !!}";
    var annoDeleteAPI = "{!! $annoAPI['delete'] !!}";
    var annoSearchAPI = "{!! $annoAPI['search'] !!}";
    var annoStreamAPI = "{!! $annoAPI['stream'] !!}";
    var annoCountAPI = "{!! $annoAPI['countAnnos'] !!}";
    annotationLayer_left.buildConnections(annoCreateAPI, annoGetAnnotatorsAPI, annoGetLabelsAPI,
                                          annoInsertAPI, annoReadAPI, annoUpdateAPI, annoDeleteAPI,
                                          annoSearchAPI, annoStreamAPI, annoCountAPI)
    // annotationLayer_left.buildConnections(database_url, imgid);
    annotationLayer_left.enableEditing(userId);
    annotationLayer_left.draw();  // annotationLayer.hide();

    var annotationLayer_right = new IViewerAnnotation(rightViewer, {
        'layers': [{'capacity': 2048}],
    });
    annotationLayer_right.colorPalette = colorPalette;
    annotationLayer_right.buildConnections(annoCreateAPI, annoGetAnnotatorsAPI, annoGetLabelsAPI,
                                          annoInsertAPI, annoReadAPI, annoUpdateAPI, annoDeleteAPI,
                                          annoSearchAPI, annoStreamAPI, annoCountAPI);
    annotationLayer_right.draw();

</script>

<!-- Save/Load Annotations (not used in phase3)-->
<script>
        /**
         *
         * ----- Annotation Section Start ------
         *
         */
        var loadanno, annofile, annoVisible;
        var leftAnnoNewList = [], rightAnnoNewList = [], annoNewResult = {};
        var annos;

        //load annotations
        // var loadLatestAnnotation = function () {
        //     $.ajax({
        //         type: 'POST',
        //         url: "{{ url('/get-note') }}",
        //         data: {
        //             "_token": "{{ csrf_token() }}",
        //             'id': imgid,
        //             'noteid': {{ $noteid }},
        //         },
        //         dataType: 'json',
        //         success: function (response) {
        //             lastAnno(response.name, response.author, response.date, response.time);
        //             if (response.note !== null) {
        //                 leftAnno.clearAnnotations();
        //                 rightAnno.clearAnnotations();

        //                 loadanno = JSON.parse(response.note);

        //                 annos = loadanno.hasOwnProperty('original') ? loadanno['original'] : loadanno['left'];
        //                 if (annos.length > 0) {
        //                     annos.forEach(ele => {
        //                         leftAnno.addAnnotation(ele, true)
        //                         leftAnnoNewList.push(ele)
        //                     })
        //                 }

        //                 annos = loadanno.hasOwnProperty('mask') ? loadanno['mask'] : loadanno['right'];
        //                 if (annos.length > 0) {
        //                     annos.forEach(ele => {
        //                         rightAnno.addAnnotation(ele, true)
        //                         rightAnnoNewList.push(ele)
        //                     })
        //                 }

        //                 annoVisible = true;
        //             }
        //         },
        //         statusCode: {
        //             419: function () { // Page expired
        //                 window.location.href = 'login';
        //             }
        //         },
        //         error: function (xhr, status, error) {
        //             var errorMessage = xhr.status + ': ' + xhr.statusText
        //             alert('Error - ' + errorMessage);
        //         }
        //     });
        // }

        // loadLatestAnnotation();


        // function hideOrShowAnno() {
        //     if (annoVisible) {
        //         leftAnno.clearAnnotations();
        //         rightAnno.clearAnnotations();
        //         leftAnnoNewList = [];
        //         rightAnnoNewList = [];

        //         annoVisible = false;
        //     } else {
        //         loadLatestAnnotation();
        //         annoVisible = true;
        //     }
        // }

        var fullScreenFlag = false,
            imageDesc = document.getElementById("image_desc"),
            sideBar = document.getElementById("accordionSidebar"),
            pageTitle = document.getElementById("page_title"),
            pageTop = document.getElementById("show_top");

        $('.toggle-full').click(function () {
            $("i", this).toggleClass("fa-compress-arrows-alt fa-expand-arrows-alt");
        });

        function fullScreen() {
            if (fullScreenFlag) {
                imageDesc.style.display = "block";
                // lastAnnoShown.style.display = "block";
                sideBar.style.display = "block";
                pageTitle.style.display = "block";
                pageTop.style.display = "";
                // buttonWarning.style.display = "block";
                $("#viewer").css("height", "600px");
                fullScreenFlag = false;
            } else {
                imageDesc.style.display = "none";
                // lastAnnoShown.style.display = "none";
                sideBar.style.display = "none";
                pageTitle.style.display = "none";
                pageTop.style.display = "none";
                // buttonWarning.style.display = "none";
                var setHeight = $(window).height() * 0.75;
                $("#viewer").css("height", setHeight);
                fullScreenFlag = true;
            }
        }

        function roundFloatsInSVG(svgString) {
            // Regular expression to match floating-point numbers in the points attribute
            var floatRegex = /(\d+\.\d+)/g;

            // Replace each match with its rounded integer value
            var roundedSVG = svgString.replace(floatRegex, function(match) {
                return parseFloat(match).toFixed(3);
            });

            return roundedSVG;
        }

        // $("#saveModal").on('show.bs.modal', function () {
        //     const today = new Date();
        //     const date1 = today.getFullYear() + '' + (today.getMonth() + 1) + '' + today.getDate() + '' + today.getHours() + '' + today.getMinutes() + '' + today.getSeconds();

        //     $("[name='name']").val("{{ Auth::user()->name }}_" + date1);
        // });

        // $(document).on('click', '.save-annotation', function (e) {
        //     e.preventDefault();
        //     annoNewResult['left'] = leftAnno.getAnnotations();
        //     annoNewResult['left'].forEach(function(annotation) {
        //         annotation.target.selector.value = roundFloatsInSVG(annotation.target.selector.value);
        //     });
        //     annoNewResult['right'] = rightAnno.getAnnotations();
        //     annoNewResult['right'].forEach(function(annotation) {
        //         annotation.target.selector.value = roundFloatsInSVG(annotation.target.selector.value);
        //     });


        //     if (Object.keys(annoNewResult).length === 0) {
        //         $('#saveModal').modal('hide');
        //         $("[name='name']").val("");

        //         $(".save-msg").html('<span class="text-danger"><i class="fas fa-exclamation-circle"></i> No Annotation</span>').show().delay(3000).fadeOut();
        //     } else if (annoNewResult['left'].length === 0 && annoNewResult['right'].length === 0) {
        //         $('#saveModal').modal('hide');
        //         $("[name='name']").val("");

        //         $(".save-msg").html('<span class="text-danger"><i class="fas fa-exclamation-circle"></i> Saving null annotations is not permitted.</span>').show().delay(3000).fadeOut();
        //     } else {

        //         $(".save-msg").text('Saving..');

        //         var data = {
        //             "_token": "{{ csrf_token() }}",
        //             'name': $("[name='name']").val(),
        //             'id': imgid,
        //             'note': JSON.stringify(annoNewResult),
        //         }

        //         $.ajax({
        //             type: "POST",
        //             url: "{{ url('/save-note') }}",
        //             data: data,
        //             dataType: "json",
        //             success: function (response) {
        //                 var notice = '';
        //                 if (response.status == 200) {

        //                     // lastAnno(response.name, response.author, response.date, response.time);
        //                     $('#saveModal').modal('hide');
        //                     $("[name='name']").val("");

        //                     $(".save-msg").html('<span class="text-success"><i class="fas fa-check-circle fa-lg"></i> Save successfully</span>').show().delay(3000).fadeOut();

        //                     // historyDT.ajax.reload();
        //                     // leftAnnoNewList = [];
        //                     // rightAnnoNewList = [];
        //                     // annoNewResult = {};
        //                     // loadLatestAnnotation();

        //                     window.location.href = {!! json_encode(url('/')) !!} + "/slides/" + imgid + "/0";

        //                 } else if (response.status == 400) {
        //                     $("[name='name']").addClass('is-invalid');
        //                     $(".invalid-feedback").html('<strong>' + response.error.name[0] + '</strong>');

        //                     $(".save-msg").html('');
        //                 } else {
        //                     $(".save-msg").html('<span class="text-danger"><i class="fas fa-exclamation-circle"></i> Save failed</span>').show().delay(3000).fadeOut();
        //                     $('#saveModal').modal('hide');
        //                     $("[name='name']").val("");
        //                 }

        //             },
        //             statusCode: {
        //                 419: function () { // Page expired
        //                     window.location.href = 'login';
        //                 }
        //             },
        //             error: function (xhr, status, error) {
        //                 var errorMessage = xhr.status + ': ' + xhr.statusText
        //             }
        //         });
        //     }
        // });


        // Show last annotation information
        // function lastAnno(name, author, date, time) {
        //     if (author == null || date == null) {
        //         $('.last-anno').html('<small><i class="fas fa-info-circle"></i> No annotation</small>')
        //     } else {
        //         $('.last-anno').html('<small><i class="fas fa-info-circle"></i> Annotation ' +
        //             '<span class="text-primary font-weight-bold">' + name + '</span> was created by ' +
        //             '<span class="text-primary font-weight-bold">' + author + '</span> on ' +
        //             '<span class="text-primary font-weight-bold">' + date + '</span> at ' +
        //             '<span class="text-primary font-weight-bold">' + time + '</span>' +
        //             '</small>')
        //     }
        // }
</script>

<!-- scripts for annotation tool -->
<script>
    var helpMessage = createOverlayElement(leftViewer);
    var layerQueuesCheckInterval;
    function handleCheckboxClick(viewer, btn, url, btnText, id) {
            if (btn.classList.contains("active")) {
                // Call the function for button click, start to predict
                annotationLayer_left.addAnnotator(id);
                btn.classList.remove("btn-secondary");
                btn.classList.add("btn-primary");
                if(btnText == "HDYolo"){
                    btn.querySelector('.fa.fa-cog').classList.add('fa-spin');
                    layerQueuesCheckInterval = setInterval(checkViewportLevelAndDisplayHelpMessage, 1000);
                    addTile(viewer, url);
                }else if (btnText == "llava"){
                    Annotorious.SelectorPack(annotationLayer_left._annotoriousLayer);
                    annotationLayer_left._annotoriousLayer.setDrawingEnabled(true);
                    annotationLayer_left._annotoriousLayer.setDrawingTool('rect');
                }
               
            } else {
                btn.classList.remove("btn-primary");
                btn.classList.add("btn-secondary");
                if(btnText == "HDYolo"){
                    btn.querySelector('.fa.fa-cog').classList.remove('fa-spin');
                    clearInterval(layerQueuesCheckInterval); 
                    hideInstructions(helpMessage);
                    removeTile(viewer, url);
                }
                else if (btnText == "llava"){
                    annotationLayer_left._annotoriousLayer.setDrawingEnabled(false);
                }
            }
            //get the list of active checkboxes in the second group
            const activeCheckboxes = document.querySelectorAll('.model-btn-grp button.active');

    }
    function checkViewportLevelAndDisplayHelpMessage() {
        var currentLevel = leftViewer.viewport.getZoom(true); 
        var desiredLevel=5
        // if (currentLevel < desiredLevel) {
        showInstructions(helpMessage, "Zoom in to see HDYolo result");
        // }else{
        //     hideInstructions(helpMessage)
        // }
    }
        // select manual annotation tools event (cancel selected annotorious)
    document.querySelectorAll(".a9s-toolbar-btn").forEach((btnTip) => {
            btnTip.addEventListener("click", (e) => {

                if ($("#floatingWindow-left").hasClass("ui-dialog-content")) {
                    // The dialog is open, so close it
                    $("#floatingWindow-left").dialog("close");
                }
                if ($("#floatingWindow-right").hasClass("ui-dialog-content")) {
                    // The dialog is open, so close it
                    $("#floatingWindow-right").dialog("close");
                }
            });
    });

    document.querySelectorAll('.model-btn-grp button').forEach((btn) => {
            btn.addEventListener("click", (e) => {
                if ($("#floatingWindow-left").hasClass("ui-dialog-content")) {
                    // The dialog is open, so close it
                    $("#floatingWindow-left").dialog("close");
                }
                btn.classList.toggle("active");
                // const { id, path } = modelBtnData[nodeIdToSelect];
                const selectedBtn = modelBtnData.find(item => item.id === e.currentTarget.id);
                if (selectedBtn) {
                    // Access the properties of the selected object
                    const { id, text, path } = selectedBtn;
                    handleCheckboxClick(leftViewer, e.currentTarget, path, text, id);
                }
            });
    });

</script>

<!-- Annotation History Table (not used in phase3)-->
<!-- <script>
        //--------------- Load Annotation Table Start-----------------//
        var historyDT = $('#dataTable').DataTable({
            ajax: "{{ url('/history') }}" + '/' + imgid,
            columns: historyColumns,
            "scrollX": true,
            "order": [[3, "desc"]],
            columnDefs: [
                {bSortable: false, targets: [0,4]} // Disable sorting on columns
            ],
            dom: '<"wrapper"Bf><t><"wrapper mt-3"lip>',
            buttons: [{
                text: '<i class="fa fa-download" style="margin-right: 5px"></i>Download as JSON',
                attr: {
                    id: 'download_history_json',
                    class: 'btn btn-download btn-sm'
                },
            },
                {
                    text: '<i class="fa fa-download" style="margin-right: 5px"></i>Download as XML',
                    attr: {
                        id: 'download_history_xml',
                        class: 'btn btn-download btn-sm'
                    }
                }
            ]
        });

        $('.dataTables_scrollHead thead input').on('click', function () {
            if ($(this).prop("checked")) {
                $('#dataTable tbody input').each(function () {
                    if (!$(this).prop("checked")) {
                        $(this).prop("checked", true);
                        $(this).addClass('active');
                        $('#dataTable tbody tr').addClass('selected');
                    }
                });
            } else {
                $('#dataTable tbody input').each(function () {
                    if ($(this).prop("checked")) {
                        $(this).prop("checked", false);
                        $(this).removeClass('active');
                        $('#dataTable tbody tr').removeClass('selected');
                    }
                });
            }
        });

        $('#dataTable').on('click', 'tr', function (e) {
            let each_checkbox = $(this).children('td').children('input');
            if (each_checkbox.hasClass("active")) {
                each_checkbox.prop("checked", false);
                each_checkbox.removeClass('active');
                $(this).removeClass('selected');
            } else {
                each_checkbox.prop("checked", true);
                each_checkbox.addClass('active');
                $(this).addClass('selected');
            }
            ;

            let all_checked = true;
            $('#dataTable .each_checkbox').each(function () {
                if (!$(this).prop('checked')) {
                    $('.dataTables_scrollHead thead input').prop('checked', false);
                    all_checked = false;
                    return false;
                }
            });
            if (all_checked) $('.dataTables_scrollHead thead input').prop('checked', true);
        });
        //--------------- Load Annotation Table End-----------------//

        //--------------- JSON to XML Conversion Start-----------------//
        function convert(json) {
            let data = JSON.parse(json);
            let xml = convertToImageScopeXML(data);
            return xml;
        }

        function convertToImageScopeXML(data) {
            let id1 = 0;
            let xml = `<Annotations MicronsPerPixel="0.499000">\n` +
                `<Annotation Id="1" Name="" ReadOnly="0" NameReadOnly="0" LineColorReadOnly="0" Incremental="0" Type="4" LineColor="65535" Visible="1" Selected="1" MarkupImagePath="" MacroName="">\n` +
                `<Attributes>\n` +
                `<Attribute Name="Description" Id="0" Value=""/>\n` +
                `</Attributes>\n` +
                `<Regions>\n` +
                `<RegionAttributeHeaders>\n` +
                `<AttributeHeader Id="9999" Name="Region" ColumnWidth="-1"/>\n` +
                `<AttributeHeader Id="9997" Name="Length" ColumnWidth="-1"/>\n` +
                `<AttributeHeader Id="9996" Name="Area" ColumnWidth="-1"/>\n` +
                `<AttributeHeader Id="9998" Name="Text" ColumnWidth="-1"/>\n` +
                `<AttributeHeader Id="1" Name="Description" ColumnWidth="-1"/>\n` +
                `</RegionAttributeHeaders>\n`;

            data.left.forEach((annotation, index) => {
                const id = index + 1;
                const selector = annotation.target.selector;
                const text = annotation.body;
                let vertices = "";
                let type = 0;

                if (selector.type === "FragmentSelector") {
                    const [x, y, width, height] = selector.value.split(":")[1].split(",").map(Number);
                    vertices = `<Vertex X="${x}" Y="${y}" Z="0"/>\n` +
                        `<Vertex X="${x + width}" Y="${y}" Z="0"/>\n` +
                        `<Vertex X="${x + width}" Y="${y + height}" Z="0"/>\n` +
                        `<Vertex X="${x}" Y="${y + height}" Z="0"/>`;
                    type = 1;
                } else if (selector.type === "SvgSelector") {
                    const parser = new DOMParser();
                    const svgDoc = parser.parseFromString(selector.value, "image/svg+xml");
                    const polygon = svgDoc.querySelector("polygon");
                    const circle = svgDoc.querySelector("circle");
                    const ellipse = svgDoc.querySelector("ellipse");
                    const path = svgDoc.querySelector("path");

                    if (polygon) {
                        const points = polygon.getAttribute("points").split(" ").map(point => point.split(","));
                        points.push(points[0]); // add the first point to the end of the points array
                        vertices = points.map(point => `<Vertex X="${point[0]}" Y="${point[1]}" Z="0"/>`).join("\n");
                        type = 0;
                    } else if (circle) {
                        const cx = Number(circle.getAttribute("cx"));
                        const cy = Number(circle.getAttribute("cy"));
                        const r = Number(circle.getAttribute("r"));
                        vertices = `<Vertex X="${cx - r}" Y="${cy - r}" Z="0"/>\n` +
                            `<Vertex X="${cx + r}" Y="${cy + r}" Z="0"/>`;
                        type = 2;
                    } else if (ellipse) {
                        const cx = Number(ellipse.getAttribute("cx"));
                        const cy = Number(ellipse.getAttribute("cy"));
                        const rx = Number(ellipse.getAttribute("rx"));
                        const ry = Number(ellipse.getAttribute("ry"));
                        vertices = `<Vertex X="${cx - rx}" Y="${cy - ry}" Z="0"/>\n` +
                            `<Vertex X="${cx + rx}" Y="${cy + ry}" Z="0"/>`;
                        type = 2;
                    } else if (path) {
                        // Split the path into commands
                        let commands = path.getAttribute("d").split(' ');

                        // Process each command
                        for (let i = 0; i < commands.length; i++) {
                            // Get the command type (the first character)
                            let type = commands[i].charAt(0);

                            // Only process 'M' and 'L' commands
                            if (type === 'M' || type === 'L') {
                                // Convert the arguments to numbers
                                let x = Number(commands[i].slice(1));
                                let y = Number(commands[i + 1]);

                                // Add a vertex for this command
                                vertices += `<Vertex X="${x}" Y="${y}" Z="0"/>\n`;

                                // Skip the next command because we already processed it
                                i++;
                            }
                        }

                        type = 0;
                    }
                    id1 = id;
                }

                // append content and tag
                let comment_n_tag = '';
                let counter = 0;
                for (let arr of text) {
                    if (counter > 0) comment_n_tag += ', ';
                    if (arr.purpose === 'commenting') {
                        comment_n_tag += 'comment:' + arr.value;
                    } else if (arr.purpose === 'tagging') {
                        comment_n_tag += 'tag:' + arr.value;
                    }
                    counter += 1;
                }

                xml += `<Region Id="${id}" Type="${type}" Zoom="0.669026" Selected="0" ImageLocation="" ImageFocus="-1" Length="0" Area="0" LengthMicrons="0" AreaMicrons="0" Text="${comment_n_tag}" NegativeROA="0" InputRegionId="0" Analyze="1" DisplayId="${id}">\n` +
                    `<Attributes></Attributes>\n` +
                    `<Vertices>\n` +
                    vertices +
                    `</Vertices>\n` +
                    `</Region>\n`;
            });

            data.right.forEach((annotation, index) => {
                const id = id1 + index + 1;
                const selector = annotation.target.selector;
                const text = annotation.body;
                let vertices = "";
                let type = 0;

                if (selector.type === "FragmentSelector") {
                    const [x, y, width, height] = selector.value.split(":")[1].split(",").map(Number);
                    vertices = `<Vertex X="${x}" Y="${y}" Z="0"/>\n` +
                        `<Vertex X="${x + width}" Y="${y}" Z="0"/>\n` +
                        `<Vertex X="${x + width}" Y="${y + height}" Z="0"/>\n` +
                        `<Vertex X="${x}" Y="${y + height}" Z="0"/>`;
                    type = 1;
                } else if (selector.type === "SvgSelector") {
                    const parser = new DOMParser();
                    const svgDoc = parser.parseFromString(selector.value, "image/svg+xml");
                    const polygon = svgDoc.querySelector("polygon");
                    const circle = svgDoc.querySelector("circle");
                    const ellipse = svgDoc.querySelector("ellipse");
                    const path = svgDoc.querySelector("path");

                    if (polygon) {
                        const points = polygon.getAttribute("points").split(" ").map(point => point.split(","));
                        points.push(points[0]); // add the first point to the end of the points array
                        vertices = points.map(point => `<Vertex X="${point[0]}" Y="${point[1]}" Z="0"/>`).join("\n");
                        type = 0;
                    } else if (circle) {
                        const cx = Number(circle.getAttribute("cx"));
                        const cy = Number(circle.getAttribute("cy"));
                        const r = Number(circle.getAttribute("r"));
                        vertices = `<Vertex X="${cx - r}" Y="${cy - r}" Z="0"/>\n` +
                            `<Vertex X="${cx + r}" Y="${cy + r}" Z="0"/>`;
                        type = 2;
                    } else if (ellipse) {
                        const cx = Number(ellipse.getAttribute("cx"));
                        const cy = Number(ellipse.getAttribute("cy"));
                        const rx = Number(ellipse.getAttribute("rx"));
                        const ry = Number(ellipse.getAttribute("ry"));
                        vertices = `<Vertex X="${cx - rx}" Y="${cy - ry}" Z="0"/>\n` +
                            `<Vertex X="${cx + rx}" Y="${cy + ry}" Z="0"/>`;
                        type = 2;
                    } else if (path) {
                        // Split the path into commands
                        let commands = path.getAttribute("d").split(' ');

                        // Process each command
                        for (let i = 0; i < commands.length; i++) {
                            // Get the command type (the first character)
                            let type = commands[i].charAt(0);

                            // Only process 'M' and 'L' commands
                            if (type === 'M' || type === 'L') {
                                // Convert the arguments to numbers
                                let x = Number(commands[i].slice(1));
                                let y = Number(commands[i + 1]);

                                // Add a vertex for this command
                                vertices += `<Vertex X="${x}" Y="${y}" Z="0"/>\n`;

                                // Skip the next command because we already processed it
                                i++;
                            }
                        }
                        type = 0;
                    }
                }

                // append content and tag
                let comment_n_tag = '';
                let counter = 0;
                for (let arr of text) {
                    if (counter > 0) comment_n_tag += ', ';
                    if (arr.purpose === 'commenting') {
                        comment_n_tag += 'comment:' + arr.value;
                    } else if (arr.purpose === 'tagging') {
                        comment_n_tag += 'tag:' + arr.value;
                    }
                    counter += 1;
                }

                xml += `<Region Id="${id}" Type="${type}" Zoom="0.669026" Selected="0" ImageLocation="" ImageFocus="-1" Length="0" Area="0" LengthMicrons="0" AreaMicrons="0" Text="${comment_n_tag}" NegativeROA="0" InputRegionId="0" Analyze="1" DisplayId="${id}">\n` +
                    `<Attributes></Attributes>\n` +
                    `<Vertices>\n` +
                    vertices +
                    `</Vertices>\n` +
                    `</Region>\n`;
            });

            xml += `</Regions>\n` +
                `<Plots></Plots>\n` +
                `</Annotation>\n` +
                `</Annotations>`;

            return xml;
        }
        //--------------- JSON to XML Conversion End-----------------//

        //--------------- Download Annotation Start-----------------//
        $('#download_history_json, #download_history_xml, #download_cur_json, #download_cur_xml').on('click', function () {
            let ids = [];
            if (this.id === 'download_cur_json' || this.id === 'download_cur_xml') {
                ids = {{$noteid}};
            } else {
                $('#dataTable tbody input').each(function () {
                    if ($(this).prop('checked')) {
                        ids.push(this.value);
                    }
                });
                if (ids.length == 0) {
                    $('.alert-danger').show();
                    return false;
                }
            }
            $('.alert-danger').hide();

            let format = 'json';
            let extension = '.json';
            if (this.id === 'download_history_xml' || this.id === 'download_cur_xml') {
                format = 'xml';
                extension = '.xml';
            }

            $.ajax({
                url: '{{ url("/download") }}' + '/' + imgid + '/' + ids,
                success: function (data) {
                    let json;
                    // download single file
                    if (data.length === 1) {
                        let zip = new JSZip();
                        json = (format === 'xml') ? convert(data[0][1]) : data[0][1];
                        let blob = new Blob([json], {
                            type: 'text/json'
                        });
                        let url = URL.createObjectURL(blob);
                        let a = document.createElement('a');
                        a.href = url;
                        a.download = data[0][0] + extension;
                        a.click();
                    }
                    // download and zip multiple files
                    else if (data.length > 1) {
                        let zip = new JSZip();
                        for (let val of data) {
                            json = (format === 'xml') ? convert(val[1]) : val[1];
                            zip.file(val[0] + extension, json);
                        }

                        zip.generateAsync({type: "blob"}).then(function (content) {
                            // // Force down of the Zip file
                            saveAs(content, "annotations.zip");
                        });
                    }
                }
            });
        });
        //--------------- Download Annotation End-----------------//

        /**
         *
         * ----- Annotation Section End ------
         *
         */

</script> -->

<!-- Overlay Layers Section  -->
<script>
    let userIdNameMap=@json($userIdMap);
    async function fetchData() {
        try {
            const response = await fetch('{{ route('fetch-annotator') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',

                        // Add any additional headers if necessary
                },
                    // If you need to send any data with the request, you can include a body property
                    // body: JSON.stringify({key: value}),
            });
                const data = await response.json();
                // Handle the response data, for example, updating the UI
                // document.getElementById('result').innerText = JSON.stringify(data);
        } catch (error) {
                console.error('Error fetching data:', error);
        }
    }

    //set default selected annotators
    let api = annotationLayer_left.APIs.annoGetAnnotators;
    let tablequery={"annotator":[]};   
    var initialannotatorids = new Set();
    getAnnotators(api).then(annotatorIds => {
        let annotatorsMap = {};
        annotatorIds.forEach(key => {
            annotatorsMap[key] = userIdNameMap[key];
        });
        for (const key in annotatorsMap) {
            if (annotatorsMap[key] === null || typeof annotatorsMap[key] === 'undefined') {
                continue;
            }
            if (!isNaN(key)) { // Check if key is a number
                initialannotatorids.add(key)
                tablequery.annotator.push(key);
                    // annotationLayer_left.addAnnotator(key); 
            } 
        }
        annotationLayer_left.updateAnnotators(initialannotatorids);
        drawNUpdateDatatable(annotationLayer_left.APIs.annoSearchAPI, tablequery, colorPalette);   
        
    })

    annotatorTreeBtn_left.addHandler('click', function(event) {
        $("#floatingWindow-left").dialog({
            // autoOpen: false,
            open: function(event, ui) {
                // Remove the hidden class when the dialog is opened
                $(".zoomin-message-left").removeClass("hidden");
            }
        });
        annotatorTreeBtnClick(annotationLayer_left, userIdNameMap, "#layers-left", colorPalette);
    });
    annotatorTreeBtn_right.addHandler('click', function(event){
        $("#floatingWindow-right").dialog({
            // autoOpen: false,
            open: function(event, ui) {
                // Remove the hidden class when the dialog is opened
                $(".zoomin-message-right").removeClass("hidden");
            }
        });
        annotatorTreeBtnClick(annotationLayer_right, userIdNameMap, "#layers-right", colorPalette);
    });
        /**
         *
         * ----- Overlay Layers Section End ------
         *
         */
</script>
@endsection
