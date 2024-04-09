/*
 * Javascript: EventON functions for all calendars
 * @version: 4.6.1
 */
(function($){

	// Calendar function s
		$.fn.evo_cal_functions = function(O){
			el = this;
			switch(O.action){
				// load shortcodes inside calendar data
				case 'load_shortcodes':
					return el.find('.evo_cal_data').data('sc');		
				break;
				case 'update_json':
					//console.log(O.json);
					el.find('.evo_cal_events').data('events', O.json);		
				break;
				case 'update_shortcodes':
					//console.log(O.SC);
					el.find('.evo_cal_data').data( 'sc', O.SC );
				break;
			}
		};

	// access page GLOBALS
		$.fn.evo_get_global = function(opt){
			var defaults = { S1:'', S2:''};
			var OPT = $.extend({}, defaults, opt);

			var BUS = $('#evo_global_data').data('d');

			if(!(OPT.S1 in BUS)) return false;
			if(!(OPT.S2 in BUS[OPT.S1])) return false;
			return BUS[OPT.S1][OPT.S2];
		}
		$.fn.evo_get_txt = function(opt){
			var defaults = { V:''}
			var OPT = $.extend({}, defaults, opt);

			var BUS = $('#evo_global_data').data('d');
			//console.log(BUS);
			if(!('txt' in BUS)) return false;
			if(!(OPT.V in BUS.txt)) return false;
			return BUS.txt[OPT.V];
		}
		$.fn.evo_get_cal_def = function(opt){
			var defaults = { V:''}
			var OPT = $.extend({}, defaults, opt);

			var BUS = $('#evo_global_data').data('d');
			if(!('cal_def' in BUS)) return false;
			if(!(OPT.V in BUS.cal_def)) return false;
			return BUS.cal_def[OPT.V];
		}

		// return dms translates values from global data
		// added 4.0
		$.fn.evo_get_dms_vals = function(opt){
			// type = d, d1,d3, m, m3
			// V = 0-x
			var defaults = { type:'d', V:''}
			var OPT = $.extend({}, defaults, opt);

			var BUS = $('#evo_global_data').data('d');			
			if(!('dms' in BUS)) return false;
			if(!(OPT.type in BUS.dms)) return false;

			return BUS.dms[ OPT.type ][ OPT.V ];
			
		}

	// GENERAL AJAX ACCESS + 4.1.2
		$.fn.evo_admin_get_ajax = function(opt){
  			var defs = {
  				'lightbox_key':'', // same as lightbox class
  				'lightbox_loader': true,
  				'load_new_content': true,
  				'load_new_content_id': '', // @since 4.3.5
  				'hide_lightbox':false,	// @since 4.3.5
  				'hide_message':2000,	// @since 4.3.5
  				'ajaxdata':{
  					'load_lbcontent':'',
  					'load_new_content':''
  				},
  				'uid':'',
  				'end':'admin', // admin or client
  				'loader_el':'',
  				'ajax_type':'ajax',
  				'ajax_action':'',
  			}

  			var el = $(this);
  			var OO = $.extend({}, defs, opt);
  			
  			var ajaxdata = OO.ajaxdata;

  			// passing nonce
  			ajaxdata['nn'] = ( OO.end == 'client' ) ? evo_general_params.n : evo_admin_ajax_handle.postnonce; 

  			// uid use ajax_action as backup
  				if( OO.uid == '' && OO.ajax_action != '') OO.uid = OO.ajax_action;

  			LB = false;
  			if( OO.lightbox_key != '') LB = $('body').find('.evo_lightbox.'+ OO.lightbox_key);


  			var returnvals = '';

  			// process action value
  			if( 'a' in ajaxdata ) ajaxdata['action'] = ajaxdata.a;

  			// add uid into ajax data
  			ajaxdata['uid'] = OO.uid;

  			// ajax url processing 
  				var ajax_url = el.evo_get_ajax_url({a: OO.ajax_action, e: OO.end, type: OO.ajax_type});

  			//console.log(ajaxdata);
  			//console.log(ajax_url);
			$.ajax({
				beforeSend: function(){
					$('body').trigger('evo_ajax_beforesend_' + OO.uid ,[ OO, el ]);
					if( LB && OO.lightbox_loader){
						LB.find('.ajde_popup_text').addClass( 'evoloading loading');
						LB.find('.evolb_content').addClass( 'evoloading loading');
					}
					if( OO.loader_el){
						$( OO.loader_el ).addClass('evoloading loading');
					}
				},
				type: 'POST',
				url: ajax_url,
				data: ajaxdata,
				dataType:'json',
				success:function(data){	
					//return;			
					//console.log(OO);
					if( LB ){
						// show message
							if( 'msg' in data && data.msg != '' && LB){
								LB.evo_lightbox_show_msg({'type': data.status, 
									'message':data.msg, 
									hide_lightbox: OO.hide_lightbox,	
									hide_message: OO.hide_message
								});
							}							

						// populate content
						if( OO.ajaxdata.load_lbcontent || OO.ajaxdata.load_new_content || OO.load_new_content ){
							// populate a specific dom element with content
							if( OO.load_new_content_id != '' ){
								$('body').find('#'+OO.load_new_content_id ).replaceWith( data.content );
							}else{
								LB.evo_lightbox_populate_content({content: data.content});
							}							
						}					
					}else{
						// populate content
						if( OO.ajaxdata.load_lbcontent || OO.ajaxdata.load_new_content || OO.load_new_content ){
							// populate a specific dom element inside with content 
							if( OO.load_new_content_id != '' ){
								$('body').find('#'+OO.load_new_content_id ).html( data.content );
							}							
						}
					}

					// for SP content @since 4.5.2
						if( 'sp_content' in data){
							$("body").find('#evops_content').html( data.sp_content);
						}
					// SP footer content @since 4.5.2
						if( 'sp_content_foot' in data){
							$("body").find('.evosp_foot').html( data.sp_content_foot);
						}
					// process trumbowyg editors
						$('body').trigger('evo_elm_load_interactivity');

					//console.log(OO);
					$('body').trigger('evo_ajax_success_' + OO.uid,[ OO, data , el]);	

				},complete:function(){
					$('body').trigger('evo_ajax_complete_' + OO.uid ,[ OO , el ]);
					if( LB && OO.lightbox_loader){
						LB.find('.ajde_popup_text').removeClass( 'evoloading loading');
						LB.find('.evolb_content').removeClass( 'evoloading loading');
					}
					if( OO.loader_el){
						$( OO.loader_el ).removeClass('evoloading loading');
					}
				}
			});	
		}

		// submit forms via ligtbox
		// @since 4.2.2		@updated 4.3.5
		$.fn.evo_ajax_lightbox_form_submit = function(opt){
			var defs = {
  				'lightbox_key':'', // lightbox class
  				'lightbox_loader': true, // show lightbox loading animations
  				'uid':'',
  				'end':'admin', // admin or client
  				'hide_lightbox':false,
  				'hide_message':false,
  				'load_new_content':false,
  				'load_new_content_id':'',
  				'ajax_type':'ajax',
  				'ajax_action':'',
  			}

  			var OO = $.extend({}, defs, opt);
  			const el = this;

  			const form = this.closest('form');
  			var LB = false;
  			if( OO.lightbox_key != '') LB = $('body').find('.evo_lightbox.'+ OO.lightbox_key);

  			if( LB) LB.evo_lightbox_hide_msg();

  			//console.log(OO);

  			var ajax_url = el.evo_get_ajax_url({a: OO.ajax_action, e: OO.end, type: OO.ajax_type});

			form.ajaxSubmit({
				beforeSubmit: function(opt, xhr){
					$('body').trigger('evo_ajax_beforesend_' + OO.uid ,[ OO , xhr, opt]); // 4.4.2
					if( LB && OO.lightbox_loader) LB.evo_lightbox_start_inloading();
				},
				dataType: 	'json',	
				url: ajax_url,	type: 	'POST',
				success:function(data){

					$('body').trigger('evo_ajax_success_' + OO.uid,[ OO, data, el ]);	

					if( data.status == 'good'){
						if(LB && 'msg' in data){
							LB.evo_lightbox_show_msg({'type': 'good', 
								'message':data.msg, 
								hide_lightbox: OO.hide_lightbox,
								hide_message: OO.hide_message
							});
						}

						// populate specific element
						if( OO.load_new_content && OO.load_new_content_id != ''){
							$('body').find('#'+OO.load_new_content_id ).replaceWith( data.content );
						}else{
							// populate current lightbox
							if( OO.load_new_content ) LB.evo_lightbox_populate_content({content: data.content});
						}
						
						
						// if ajax data pass dom content to be replaced with run through each and replace - @4.2.3
						if( 'refresh_dom_content' in data ){
							$.each(data.refresh_dom_content, function(domid, content){
								$('body').find('#'+ domid).replaceWith( content);
							});
						}

					}else{
						LB.evo_lightbox_show_msg({'type': 'bad', 'message':data.msg});
					}
				},
				complete:function(){	
					$('body').trigger('evo_ajax_complete_' + OO.uid ,[ OO ]);
					$('body').trigger('evo_ajax_form_complete_' + OO.uid ,[ OO , form]);
					if( LB && OO.lightbox_loader) LB.evo_lightbox_stop_inloading();	
				}
			});
		}

	// LIGHTBOX version 4.2
		// @version 4.2
		$('body').on('click','.evolb_trigger', function(event){
			if( event !== undefined ){
				event.preventDefault();
				event.stopPropagation();
			}
			
			$(this).evo_lightbox_open($(this).data('lbvals'));
		});
		$('body').on('click','.evolb_close_btn', function (){
			const LB = $(this).closest('.evo_lightbox');
			LB.evo_lightbox_close();
		});

		// @since 4.2.2
		$('body').on('click','.evolb_trigger_save, .evo_submit_form', function(event){
			if( event !== undefined ){
				event.preventDefault();
				event.stopPropagation();
			}
			$(this).evo_ajax_lightbox_form_submit( $(this).data('d') );
		});

		$('body').on('click','.evo_trigger_ajax_run', function(event){
			if( event !== undefined ){
				event.preventDefault();
				event.stopPropagation();
			}
			$(this).evo_admin_get_ajax( $(this).data('d') );
		});
		// @since 4.3.3
		$('body').on('evo_lightbox_trigger', function(event, data){
			$('body').evo_lightbox_open(data);
		});

		$.fn.evo_lightbox_open = function (opt){
			var defaults = { 
				'uid':'',
				't':'', //title
				'lbc':'',// * lightbox class - REQUIRED
				'lbac':'',// ligthbox additional class 4.6
				'lbsz':'',// lightbox size = mid small
				'content':'',// passed on dynamic content
				'content_id' :'',// id to get dynamic content from page
				'ajax':'no',// use ajax to load content yes no
				'ajax_url':'',
				'd':'', // data object for ajax
				'end':'admin',// admin or client end
				'other_data':'',
				'lightbox_loader': false,
				'preload_temp_key': 'init', // 4.6
				'load_new_content': true, // @since 4.3.5
				'lb_padding': 'evopad30', // @4.3.5
				'ajax_action':'', // @4.4 pass on ajax endpoint action key
				'ajax_type':'ajax', // @4.4 ajax type, ajax, rest or endpoint
			};

			var OO = $.extend({}, defaults, opt);

			const fl_footer = OO.end == 'client' ? '<div class="evolb_footer"></div>' :'';

			// create lightbox HTML
			var html = '<div class="evo_lightbox '+OO.lbc+' '+OO.end+' '+OO.lbac+'" data-lbc="'+OO.lbc+'"><div class="evolb_content_in"><div class="evolb_content_inin"><div class="evolb_box '+OO.lbc+' '+OO.lbsz +'"><div class="evolb_header"><a class="evolb_backbtn" style="display:none"><i class="fa fa-angle-left"></i></a><p class="evolb_title">' + OO.t + '</p><span class="evolb_close_btn evolbclose "><i class="fa fa-xmark"><i></span></div><div class="evolb_content '+ OO.lb_padding +'"></div><p class="message"></p>'+fl_footer+'</div></div></div></div>';

			$('#evo_lightboxes').append( html );			

			//console.log(OO);
			
			LIGHTBOX = $('.evo_lightbox.'+ OO.lbc);

			// Open lightbox on page
				setTimeout( function(){ 
					$('#evo_lightboxes').show();
					LIGHTBOX.addClass('show');	
					$('body').addClass('evo_overflow');
					$('html').addClass('evo_overflow');
				},300);

			// show loading animation
			LIGHTBOX.evo_lightbox_show_open_animation(OO);

			//return;
				
			// Load content
			// dynamic content within the site
				if(OO.content_id != ''){					
					var content = $('#'+ OO.content_id ).html();					
					LIGHTBOX.find('.evolb_content').html( content);
				}
			// load passed on content
				if(OO.content != ''){
					LIGHTBOX.find('.evolb_content').html( OO.content);
				}

			// run ajax to load content for the lightbox inside
				if( OO.ajax == 'yes' && OO.d != ''){

					var D = {};
					D = OO.d;

					LB.evo_admin_get_ajax({
						ajaxdata: D, 
						ajax_action: OO.ajax_action,
						ajax_type: OO.ajax_type,
						lightbox_key: OO.lbc,
						uid: ( OO.uid != '' ) ? OO.uid : OO.d.uid,
						end: OO.end,
						lightbox_loader: OO.lightbox_loader,
						load_new_content: OO.load_new_content,
					});
				}

			// load content from a AJAX file			
				if( OO.ajax_url != ''){
					$.ajax({
						beforeSend: function(){},
						url:	OO.ajax_url,
						success:function(data){
							LIGHTBOX.find('.evolb_content').html( data);							
						},complete:function(){}
					});
				}
			
			$('body').trigger('evo_lightbox_processed', [ OO, LIGHTBOX]);
		}

		$.fn.evo_lightbox_close = function (opt){
			LB = this;
			var defaults = { 
				'delay':500, 
				'remove_from_dom':true,
			};

			if( !(LB.hasClass('show')) ) return;

			var OO = $.extend({}, defaults, opt);

			var hide_delay = parseInt( OO.delay);

			complete_close = (LB.parent().find('.evo_lightbox.show').length == 1)? true: false;

			if( hide_delay > 500){
				setTimeout( function(){ 
					LB.removeClass('show');
				}, ( hide_delay - 500  ) );
			}else{
				LB.removeClass('show');
			}
			
			setTimeout( function(){ 
				if(complete_close){
					$('body').removeClass('evo_overflow');
					$('html').removeClass('evo_overflow');
				}
				// remove lightbox HTML from DOM
				if( OO.remove_from_dom) LB.remove();
			}, hide_delay);	
		}


		// Other LB functions
			$.fn.evo_lightbox_populate_content = function(opt){
				LB = this;
				var defaults = { 
					'content':'',
				}; var OO = $.extend({}, defaults, opt);
				LB.find('.evolb_content').html( OO.content );
			}
			$.fn.evo_lightbox_start_inloading = function(opt){
				LB = this;
				LB.find('.evolb_content').addClass('loading');
			}
			$.fn.evo_lightbox_stop_inloading = function(opt){
				LB = this;
				LB.find('.evolb_content').removeClass('loading');
			}
			$.fn.evo_lightbox_show_msg = function(opt){
				LB = this;
				var defaults = { 
					'type':'good',
					'message':'',
					'hide_message': false,// hide message after some time pass time or false
					'hide_lightbox': false, // hide lightbox after some time of false
				}; var OO = $.extend({}, defaults, opt);
				LB.find('.message').removeClass('bad good').addClass( OO.type ).html( OO.message ).fadeIn();

				if( OO.hide_message ) setTimeout(function(){  LB.evo_lightbox_hide_msg() }, OO.hide_message );

				if( OO.hide_lightbox ) LB.evo_lightbox_close({ delay: OO.hide_lightbox });
			}
			$.fn.evo_lightbox_hide_msg = function(opt){
				LB = this;
				LB.find('p.message').hide();
			}


			// add preload animations to lightbox u4.6
			$.fn.evo_lightbox_show_open_animation = function(opt){
				LB = this;
				var defaults = { 
					'animation_type':'initial', // animation type initial or saving
					'preload_temp_key': 'init', // 4.6 passed on preload template key
					'end':'admin',
				};
				var OO = $.extend({}, defaults, opt);

				if( OO.animation_type == 'initial'){

					passed_data = OO.end == 'admin' ? evo_admin_ajax_handle: evo_general_params;

					html = passed_data.html.preload_general;
					if( OO.preload_temp_key != 'init') html = passed_data.html[ OO.preload_temp_key ];
					LB.find('.evolb_content').html( html );
				}

				if( OO.animation_type == 'saving')
					LB.find('.evolb_content').addClass('loading');
			}

		// eventcard Lightbox function v4.6
			$.fn.evo_cal_lightbox_trigger = function( SC_data , obj, CAL ){
				const cancel_class = ( obj.hasClass('cancel_event')) ? ' cancel_event':'';

				var other_data = {
					extra_classes: 'evo_lightbox_body eventon_list_event evo_pop_body evcal_eventcard event_'+SC_data.event_id +'_'+ SC_data.repeat_interval + cancel_class,
					CAL:CAL,
					obj: obj,
					et_data: obj.find('.evoet_data').data(),// @4.5.5
					SC: SC_data
				};
				
				// generate a random ID for this 
					maximum = 99;
					minimum = 10;
					var randomnumber = Math.floor(Math.random() * (maximum - minimum + 1)) + minimum;

				// lightbox additional class
					lbac = '';
					if( evo_general_params.cal.lbs == 'sc1') lbac = 'within';
					if( evo_general_params.cal.lbs == 'sc2') lbac = 'within ecSCR';

				// AJAX via lightbox
				if(SC_data.ux_val == '3a'){

					var new_content = '';
					new_content += '<div class="evo_cardlb" style="padding:10px 10px 0 10px">';
					new_content += '<div style="margin-bottom:20px; width:100%; height:200px" class="evo_preloading"></div>';
					const box = '<div style="display:flex;justify-content: space-between;margin-bottom:10px"><div style="width:40px;height:40px; margin-right:20px" class="evo_preloading"></div> <div style="flex:1 0 auto"> <div class="evo_preloading" style="width:70%; height:20px; margin-bottom:10px"></div><div class="evo_preloading" style="width:100%; height:80px; margin-bottom:10px"></div>  </div> </div>';
					new_content += box + box + box;
					new_content += '</div>';

					
					var data_arg = {};
					data_arg['event_id'] = SC_data.event_id;
					data_arg['ri'] = SC_data.repeat_interval;
					data_arg['SC'] = SC_data;
					data_arg['load_lbcontent'] = true;
					data_arg['action'] = 'eventon_load_single_eventcard_content';
					data_arg['uid'] = 'load_single_eventcard_content_3a';
					if( CAL ) data_arg['calid'] = CAL.attr('id');

					
					//reset view to match
						if( data_arg.SC.tile_style == '2') data_arg.SC.eventtop_style = '0';
						data_arg.SC.tile_style = '0';
						data_arg.SC.tile_bg = '0';
						data_arg.SC.tiles = 'no';
						
					$('body').evo_lightbox_open({
						uid:'evo_open_eventcard_lightbox',
						//uid:'load_single_eventcard_content_3a',
						lbc:'evo_eventcard_'+ randomnumber,lbac: lbac,
						end:'client',
						content: new_content,
						ajax:'yes',
						ajax_type: 'endpoint',
						ajax_action: 'eventon_load_single_eventcard_content',
						d: 	data_arg,
						other_data: other_data
					});
					
				// open lightbox using page event content
				}else{

					var content = obj.closest('.eventon_list_event').find('.event_description').html();
					var _content = $(content).not('.evcal_close');

					clrW = obj.closest('.eventon_list_event').hasClass('clrW') ? 'clrW':'clrB';//4.6.2
									
					CAL.evo_lightbox_open({
						uid:'evo_open_eventcard_lightbox',
						lbc:'evo_eventcard_'+ randomnumber,lbac: lbac,
						end:'client',
						content: '<div class="evopop_top '+clrW+'">'+ obj.html() +'</div><div class="evopop_body">' + content +'</div>',
						other_data: other_data
					});

					return;
				}	
			}

			// listerners for lightbox on eventcard
			$.fn.evo_cal_lb_listeners = function(){
				// Listerners
				// process various lightbox types
				$('body')
				.on('evo_lightbox_processed', function(event, OO, LIGHTBOX){
					if( OO.uid != 'evo_open_eventcard_lightbox') return false;

					var CAL = OO.other_data.CAL;

					LIGHTBOX.addClass('eventcard eventon_events_list');
					LIGHTBOX_content = LIGHTBOX.find('.evolb_content');
					LIGHTBOX_content.attr('class', 'evolb_content '+ OO.other_data.extra_classes );
					
					var SC = OO.other_data.SC;
					var obj = OO.other_data.obj;

					//console.log(SC);

					// update border color and eventtop color
						const evoet_data = OO.other_data.et_data;
						
						bgcolor = bggrad ='';
						if( evoet_data ){
							bgcolor = evoet_data.bgc;
							bggrad = evoet_data.bggrad;
						}
						

						// if tiles and eventtop style set to clean
						var show_lightbox_color = ( SC.eventtop_style == '0' || SC.eventtop_style == '4') ? false: true;
						
						if( (CAL && CAL.hasClass('color') && show_lightbox_color) ||
							(!CAL && show_lightbox_color)

						){
							LIGHTBOX_content.addClass('color');
							LIGHTBOX_content.find('.evopop_top').css({
								'background-color':bgcolor,
								'background-image': bggrad,
							});
						}else{
							LIGHTBOX_content.find('.evopop_top').css({'border-left':'3px solid '+bgcolor});
						}

					// trigger 
					if( obj.data('runjs')){
						$('body').trigger('evo_load_single_event_content',[ SC.event_id, OO.other_data.obj]);
					}
									
					
					// RTL
					if( SC.evortl =='yes')	LIGHTBOX.addClass('evortl');

					$('body').trigger('evolightbox_end', [ LIGHTBOX , CAL, OO]);	// @s4.6

				})

				// after eventcard content is loaded to lightbox via 3a - @since 4.2.3
					.on('evo_ajax_success_evo_open_eventcard_lightbox', function (event, OO, data){
						
						if( OO.ajaxdata.uid != "load_single_eventcard_content_3a") return false;
						
						LIGHTBOX = $('.evo_lightbox.'+ OO.lightbox_key);
						LIGHTBOX_content = LIGHTBOX.find('.evolb_content');

						CAL = $('body').find('#'+ OO.ajaxdata.calid);
						
						$('body').trigger('evolightbox_end', [ LIGHTBOX , CAL, OO]);	// @s4.6	
					})
				;			
			}

	// Get Ajax url @since 4.4 @u 4.5.5
		$.fn.evo_get_ajax_url = function(opt){
			var defaults = { 
				a:'', // action key
				e:'client', // end
				type: 'ajax'};
			var OO = $.extend({}, defaults, opt);

			// end point url
			if( OO.type == 'endpoint'){
				var evo_ajax_url = ( OO.e == 'client' )? 
					evo_general_params.evo_ajax_url : evo_admin_ajax_handle.evo_ajax_url;
				return  evo_ajax_url.toString().replace( '%%endpoint%%', OO.a );
			// rest api url
			}else if( OO.type == 'rest' ){
				var evo_ajax_url = ( OO.e == 'client' )? 
					evo_general_params.rest_url : evo_admin_ajax_handle.rest_url;
					//console.log(evo_ajax_url);
					//console.log(OO);
				return  evo_ajax_url.toString().replace( '%%endpoint%%', OO.a );
			}else{
				action_add = OO.a != '' ? '?action='+ OO.a: '';
				return ( OO.e == 'client' ) ? 
					evo_general_params.ajaxurl + action_add : evo_admin_ajax_handle.ajaxurl + action_add;
			}	
		}
	
	// Count down	// @+ 4.5
		$.fn.evo_countdown_get = function(opt){
			var defaults = { gap:'', endutc: ''};
			var OPT = $.extend({}, defaults, opt);
			var gap = OPT.gap;

			// if gap not provided use endutc
			if( gap == '' ){				
				var Mnow = moment().utc();
				var M = moment();
				M.set('millisecond', OPT.endutc );

				gap = OPT.endutc - Mnow.unix();
			}
			
			// if negative gap
			if( gap < 0){
				return {
					'd': 0,
					'h':0,
					'm':0,
					's':0
				}; 
			}
		
			distance = ( gap * 1000);

			var days = Math.floor(distance / (1000 * 60 * 60 * 24));
			var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
			var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
			var seconds = Math.floor((distance % (1000 * 60)) / 1000);

			minutes = minutes<10? '0'+minutes : minutes;
			seconds = seconds<10? '0'+seconds : seconds;

			return {
				'd': days,
				'h':hours,
				'm':minutes,
				's':seconds
			}; 
		};
		// @u 4.5.2
		$.fn.evo_countdown = function(opt){
			var defaults = { S1:''};
			var OPT = $.extend({}, defaults, opt);
			var el = $(this);

			const day_text = ( el.data('d') !== undefined && el.data('d') != '' )? el.data('d'):'Day';
			const days_text = ( el.data('ds') !== undefined && el.data('ds') != '' )? el.data('ds'):'Days';

			// intial run
			//var gap = parseInt(el.data('gap'));
			var duration = el.data('dur');
			var endutc = parseInt(el.data('endutc'));
			var text = el.data('t');
			
			if(text === undefined) text = '';

			if( el.hasClass('evo_cd_on')) return;

			// get gap using end time utc
				var Mnow = moment().utc();
				var M = moment();
				M.set('millisecond', OPT.endutc );

				//console.log(Mnow.unix());
				//console.log(endutc);

				gap = endutc - Mnow.unix();


			// on initial run - event hasnt happened yet
			if( gap > 0 ){

				// initial
				dd = el.evo_countdown_get({ 'gap': gap });
				
				el.html( ( dd.d>0 ? dd.d + ' ' + ( dd.d >1 ? days_text: day_text ) + " "  :'') + dd.h + ":" + dd.m +':'+ dd.s +'  '+text );
				
				el.data('gap', ( gap - 1)  );	// save new gap value
				el.addClass('evo_cd_on');
				
				// set intervals
				var CD = setInterval(function(){
					
					gap = el.data('gap'); // get gap for this cycle
					duration = el.data('dur');	

					const bar_elm = el.closest('.evo_event_progress').find('.evo_ep_bar');	

					// when timer is ticking
					if( gap > 0 ){

						// increase bar width if exists
						if( duration !== undefined && bar_elm.length){
							perc = ( (duration - gap)/ duration ) * 100;
							bar_elm.find('b').css('width',perc+'%');							
						}
						
						dd = el.evo_countdown_get({ 'gap': gap });	

						el.html( ( dd.d>0 ? dd.d + ' '  + ( dd.d >1 ? days_text: day_text ) + " ":'') + dd.h + ":" + dd.m +':'+ dd.s +' '+text );
						el.data('gap', ( gap - 1)  );

					// when timer expired
					}else{

						const expire_timer_action = el.data('exp_act');

						// perform ajax task after expiration
						if(expire_timer_action !== undefined){
							$('body').trigger('runajax_refresh_now_cal',[ 
								el , 
								el.data('n'),
							]);
						}

						const _complete_text = el.evo_get_txt({V:'event_completed'});

						// live now text
						if(bar_elm.length){
							bar_elm.addClass('evo_completed');
							//el.siblings('.evo_ep_pre').html( _complete_text );
						}


						// event tag & live elements
						if( el.closest('.evcal_desc').length){
							el.closest('.evcal_desc').find('.eventover').html( _complete_text);
							el.closest('.evcal_desc').find('.evo_live_now').remove();
						}

						// event card
						if( el.closest('.eventon_list_event').length){
							el.closest('.eventon_list_event').find('span.evo_live_now').hide();
						}

						el.html('');
						clearInterval(CD);
					}

				},1000);
			// event has already passed
			}else{
				// if gap is less
				el.closest('.evo_event_progress').find('.evo_ep_bar').hide();
				clearInterval(CD);
			}
		};	

	// Handlebar process template data into html
		$.fn.evo_HB_process_template = function(opt){
			var defaults = { TD:'', part:''}
			var OPT = $.extend({}, defaults, opt);

			BUS = $('#evo_global_data').data('d');
						
			template = Handlebars.compile( BUS.temp[ OPT.part ] );
			return template( OPT.TD );
		}

	// Date range and events @4.5.7
		// Date range and events - from webpage
		$.fn.evo_cal_events_in_range = function(opt){
			var defaults = { S:'', E:'', 
				hide: true, 
				closeEC:true,
				showEV: false, // show events
				showEVL: false, // show events list
				showAllEvs: false // show all events regardless of the range
			};
			var OPT = $.extend({}, defaults, opt);
			var CAL = $(this);

			eJSON = CAL.find('.evo_cal_events').data('events');

			R = {};
			html = '';
			json = {};

			show = 0;

			// using events JSON data
			if( eJSON && eJSON.length > 0){
				$.each(eJSON, function(ind, ED){
					eO = CAL.find('#event_'+ ED._ID);
					if(eO === undefined || eO.length==0) return;

					if(OPT.hide)	eO.hide(); // pre hide
					this_show = false;

					// month long or year long events
					if( ED.month_long || ED.year_long ){
						this_show = true;

					}else{
						if(CAL.evo_is_in_range({
							'S': OPT.S,	'E': OPT.E,	'start': ED.unix_start ,	'end':ED.unix_end 
						})){						
							this_show = true;
						} 
					}

					if( OPT.showAllEvs) this_show = true;
					
					if( this_show){	
						// show event
						if( OPT.showEV) eO.show();

						// close open event cards
						if(OPT.closeEC && SC.evc_open == 'no') eO.find('.event_description').hide().removeClass('open');

						html += eO[0].outerHTML;
						json[ ED._ID] = ED;
						show++;	
					} 
				});
			}else{	
				// get all the events in the events list
				var cal_events = CAL.find('.eventon_list_event');

				cal_events.each(function(index, elm){
					var ED = $(elm).evo_cal_get_basic_eventdata();
					if( !ED) return;

					if(OPT.hide)	$(elm).hide(); // pre hide
					this_show = false;

					// month long or year long events
					if( $(elm).hasClass('month_long') || $(elm).hasClass('year_long') ){
						this_show = true;

					}else{
						if(CAL.evo_is_in_range({
							'S': OPT.S,	'E': OPT.E,	'start': ED.unix_start ,	'end':ED.unix_end 
						})){						
							this_show = true;
						} 
					}

					if( OPT.showAllEvs) this_show = true;
					
					if( this_show){	
						// show event
						if( OPT.showEV) $(elm).show();

						// close open event cards
						if(OPT.closeEC && SC.evc_open == 'no') 
							$(elm).find('.event_description').hide().removeClass('open');

						html += $(elm)[0].outerHTML;
						json[ ED.uID ] = ED;
						show++;	
					} 
				});
			}


			// No events
			if( OPT.showEV){

				no_event_content = CAL.evo_get_global({S1: 'html', S2:'no_events'});

				tx_noevents = CAL.evo_get_txt({V:'no_events'});
				EL = CAL.find('.eventon_events_list');
				EL.find('.eventon_list_event.no_events').remove();
				if( show == 0 )
					EL.append('<div class="eventon_list_event no_events">'+ no_event_content +'</div>');
			}

			// if show events list
			if( OPT.showEVL){
				CAL.find('.eventon_events_list').show().removeClass('evo_hide');
			}

			R['count'] = show;
			R['html'] = html;
			R['json'] = json;

			return R;
		}

	// check if an event is in the given date range
		$.fn.evo_is_in_range = function(opt){
			var defaults = { S:'', E:'', start:'',end:''}
			var OPT = $.extend({}, defaults, opt);

			S = parseInt(OPT.S);
			E = parseInt(OPT.E);
			start = parseInt(OPT.start);
			end = parseInt(OPT.end);

			return (
				( start <= S && end >= E ) ||
				( start <= S && end >= S && end <= E) ||
				( start <= E && end >= E ) ||
				( start >= S && end <= E )
			) ? true: false;
		}
		$.fn.evo_cal_hide_events = function(){
			CAL = $(this);
			CAL.find('.eventon_list_event').hide();
		}

	// get event data basics from html event on page 
	// ~@version 4.0.3
		$.fn.evo_cal_get_basic_eventdata = function(){
			var ELM = $(this);

			var _time = ELM.data('time');
			if( _time === undefined ) return false;

			const time = _time.split('-');
			const ri = ELM.data('ri').replace('r','');
			const eID = ELM.data('event_id');

			var _event_title = ELM.find('.evcal_event_title').text();
			_event_title = _event_title.replace(/'/g, '&apos;');


			var RR = {
				'uID': eID + '_' + ri,
				'ID': eID ,
				'event_id': eID ,
				'ri': ri,
				'event_start_unix': parseInt(time[0]),
				'event_end_unix': parseInt(time[1]),
				'ux_val': ELM.find('.evcal_list_a').data('ux_val'),
				'event_title': _event_title,
				'hex_color': ELM.data('colr'),
				'hide_et': ELM.hasClass('no_et') ? 'y':'n', // hide endtime
				'evcal_event_color': ELM.data('colr'),
				'unix_start': parseInt(time[0]),// @4.5.7
				'unix_end': parseInt(time[1]),// @4.5.7
			};

			// event type
			RR['ett1'] = {};
			ELM.find('.evoet_eventtypes.ett1 .evoetet_val').each(function(){
				RR['ett1'][ $(this).data('id')] = $(this).data('v');
			});

			// since 4.3.5
			const eventtop_data = ELM.find('.evoet_data').data('d');

			// location
			if('loc.n' in eventtop_data && eventtop_data['loc.n'] != ''){
				RR['location'] = eventtop_data['loc.n'];
			}

			// organizer
			if('orgs' in eventtop_data && eventtop_data.orgs !== undefined ){
				var org_names = '';
				$.each(eventtop_data.orgs, function(index, value){
					org_names += value +' ';
				});
				RR['organizer'] = org_names;
			}

			// event tags @s 4.5.2
			if( 'tags' in eventtop_data && eventtop_data.tags !== undefined ){
				//console.log(eventtop_data.tags);
				RR['event_tags'] = eventtop_data.tags;
			}


			return RR;

		}


	// DATE time functions @+2.8		
		$.fn.evo_day_in_month = function(opt){
			var defaults = { M:'', Y:''}
			var OPT = $.extend({}, defaults, opt);

			return new Date(OPT.Y, OPT.M, 0).getDate();
		}
		$.fn.evo_get_day_name_index = function(opt){
			var defaults = { M:'', Y:'', D:''}
			var OPT = $.extend({}, defaults, opt);

			//return moment(OPT.Y+'-'+OPT.M+'-'+OPT.D).utc().day();

			return new Date(  Date.UTC(OPT.Y, OPT.M-1, OPT.D) ).getUTCDay();
		}

	// LIGHTBOX
	// page Lightbox functions @+2.8
	// append to the lightbox main class name .evo_lightbox		
		// Legacy
			$.fn.evo_prepare_lb = function(){
				$(this).find('.evo_lightbox_body').html('');
			}
			$.fn.evo_show_lb = function(opt){
				var defaults = { RTL:'', calid:''}
				var OPT = $.extend({}, defaults, opt);

				$(this).addClass('show '+ OPT.RTL).attr('data-cal_id', OPT.calid);
				$('body').trigger('evolightbox_show');
			}
			$.fn.evo_append_lb = function(opt){
				var defaults = { C:'', CAL:''}
				var OPT = $.extend({}, defaults, opt);
				$(this).find('.evo_lightbox_body').html( OPT.C);

				if(  OPT.CAL!= '' && OPT.CAL !== undefined && OPT.CAL.hasClass('color')){
					const LIST = $(this).find('.eventon_events_list');
					if( LIST.length>0){
						LIST.find('.eventon_list_event').addClass('color');
					}				
				}
			}

	// event image gallery @4.6
		// adjust event image height on lightbox
		$.fn.eventon_check_img_size_on_lb = function(  ){
			LB = this;
			winH = parseInt($( window ).height());
			winW = parseInt($( window ).width());

			var pad = 50;
			if( winW < 650) pad = 20;
			if( winW < 500) pad = 10;

			winH -= pad*2;
			winW -= pad*2;

			LB = $('body').find('.evolb_ft_img');
			WINratio = winH/winW;

			const IMG = LB.find('img');
			IMGratio = parseInt( IMG.data('h') )/ parseInt( IMG.data('w') );
			img_relative_w = parseInt(winH/IMGratio);

			if(WINratio < 1 ){// wider window
				if(IMGratio<1){// wider image
					if(img_relative_w > winW){ // image wider than window
						IMG.css({'width': winW ,'height': 'auto' } );
					}else{// image smaller than window width
						newIH = winH>parseInt(IMG.data('h'))? parseInt(IMG.data('h')): winH;
						newIW = (winH>parseInt(IMG.attr('h')) )? '100%': img_relative_w;
						IMG.css({'width': newIW ,'height': newIH } );
					}
				}else{
					IMG.css({'width': 'auto' ,'height': winH } );
				}
			}else{
				if(IMGratio<1){// wider image
					IMG.css({'width': winW ,'height': 'auto' } );
				}else{// taller image
					if(img_relative_w > winW){ // relative image wider than window
						IMG.css({'width': winW ,'height': 'auto' } );
					}else{
						IMG.css({'width': 'auto' ,'height': winH } );
					}
				}
			}
		}

		$.fn.eventon_process_main_ft_img = function(OO){
			IMG = this;
			img_sty = 'def';
			if( IMG.hasClass('fit') ) img_sty = 'fit';
			if( IMG.hasClass('full') ) img_sty = 'full';

			box_width = IMG.width();
			box_height = IMG.height();

			img_height = parseInt( IMG.data('h') );
			img_width = parseInt( IMG.data('w') );
			img_ratio = IR = img_height / img_width; // image ratio

			// fit style
			if( IMG.hasClass('fit')){
				new_width = box_height / img_ratio;
				//console.log(new_width+' '+box_height +' '+img_ratio);
				new_height = box_height;
				if( new_width > box_width ){// width is wider than box

					if( IR <1 ){
						new_width = box_width; new_height = IR * new_width;
					}else{
						new_height = box_height; new_width = new_height / IR;
					}
				} 				
				IMG.find('span').css({'width':new_width, 'height': new_height} );
			}

			// full style
			if( IMG.hasClass('full')){
				new_height = img_ratio * box_width;
				new_width = box_width;
				IMG.find('span').css({'width':new_width, 'height': new_height} );
				IMG.css({'height': new_height} );
			}

			//console.log(box_width +' '+img_width+' '+new_width);
		}

	// Calendar Data & Shortcodes
		// get shortcodes from evo bottom
		$.fn.evo_shortcode_data = function(){			
			var ev_cal = $(this);
			return ev_cal.find('.evo_cal_data').data('sc');			
		}

		// get filter data @4.6
			$.fn.evo_get_filter_data = function(){			
				return $(this).find('.evo_cal_data').data('filter_data');			
			}
			$.fn.evo_cal_get_filter_sub_data = function( tax , key){
				newdata = $(this).evo_get_filter_data();

				return newdata[ tax ][ key ];
			}
			$.fn.evo_cal_update_filter_data = function(tax, new_val, key){

				newdata = $(this).evo_get_filter_data();

				if( key === undefined) key = 'nterms';

				newdata[ tax ][ key ] = new_val;

				$(this).find('.evo_cal_data').data('filter_data', newdata );
			}
			$.fn.evo_cal_get_footer_data = function( key ){
				data = $(this).find('.evo_cal_data').data();

				//console.log(data);

				if( data === undefined ) return false; // 4.6.2
				if( key in data ) return data[ key ];
				return false;		
			}

		// hide cal data from view
		$.fn.evo_cal_hide_data = function(){			
			$(this).find('.evo_cal_data').attr({
				'data-sc':'',
				'data-filter_data':'',
				'data-nav_data':'',
			});			
		}

		// update shortcode values from filter changes @u4.6
		$.fn.evo_update_sc_from_filters = function(){
			var el = $(this); 	
			SC = el.evo_shortcode_data();		

			$.each( el.evo_get_filter_data() , function(index, value){
				var default_val 	= value.terms;
				var filter_val 		= value.nterms;
				const NOT_values 	= value.__notvals;

				filter_val = filter_val == ''? 'NOT-all': filter_val;	
				
				var not_string = '';

				// if NOT filter in place > include that in tax value for query
				if( NOT_values!== undefined && NOT_values.length >0 && filter_val != default_val ){	
					
					$.each(NOT_values, function(index, value){
						not_string += 'NOT-'+ value +',';
					});
				}		

				SC[ index ] = not_string + filter_val;
			});
			

			el.find('.evo_cal_data').data( 'sc', SC );
		}
		
		

		// get shortcode single value
		$.fn.evo_get_sc_val = function(opt){
			var defaults = {	F:''}
			var OPT = $.extend({}, defaults, opt);
			var ev_cal = $(this); 

			if(OPT.F=='') return false;
			SC = ev_cal.find('.evo_cal_data').data('sc');

			if(!(SC[ OPT.F])) return false;
			return SC[ OPT.F];
		}
		// UPDATE Single value
		$.fn.evo_update_cal_sc = function(opt){
			var defaults = {
				F:'', V:''
			}
			var OPT = $.extend({}, defaults, opt);
			var ev_cal = $(this); 
			SC = ev_cal.find('.evo_cal_data').data('sc');

			SC[ OPT.F ] = OPT.V;

			ev_cal.find('.evo_cal_data').data( 'sc', SC );
		}
		// UPDATE all shortcode values
		$.fn.evo_update_all_cal_sc = function(opt){
			var defaults = {SC:''}
			var OPT = $.extend({}, defaults, opt);
			var CAL = $(this);
			CAL.find('.evo_cal_data').data( 'sc', OPT.SC );
		}

	// OTHERS
		// hex colors // @+2.8
		$.fn.evo_is_hex_dark = function(opt){
			var defaults = { hex:'808080'}
			var OPT = $.extend({}, defaults, opt);

			hex = OPT.hex;

			var c = hex.replace('#','');
			var is_hex = typeof c === 'string' && c.length === 6 && !isNaN(Number('0x' + c));

			if(is_hex){	
				var values = c.split('');
				r = parseInt(values[0].toString() + values[1].toString(), 16);
			    g = parseInt(values[2].toString() + values[3].toString(), 16);
			    b = parseInt(values[4].toString() + values[5].toString(), 16);
			}else{
				var vals = c.substring(c.indexOf('(') +1, c.length -1).split(', ');
				var r = vals[0]  // extract red
				var g = vals[1];  // extract green
				var b = vals[2];
			}

			var luma = ((r * 299) + (g * 587) + (b * 114)) / 1000; // per ITU-R BT.709

			return luma>155? true:false;
		}

		// get rgb from hex code @4.5
			$.fn.evo_rgb_process = function(opt){
				var defaults = { data:'808080',type:'rgb', method:'rgb_to_val'}
				var opt = $.extend({}, defaults, opt);

				const color = opt.data;

				// RGB => hex
				if( opt.method == 'rgb_to_hex'){
					if(color == '1'){
						return;
					}else{
						if(color !=='' && color){
							rgb = color.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
							
							return "#" +
							("0" + parseInt(rgb[1],10).toString(16)).slice(-2) +
							("0" + parseInt(rgb[2],10).toString(16)).slice(-2) +
							("0" + parseInt(rgb[3],10).toString(16)).slice(-2);
						}
					}
				}

				// RGB => rgb val
				if( opt.method == 'rgb_to_val'){
					if( opt.type == 'hex'){
						var rgba = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(color);	
						var rgb = new Array();
						rgb['r']= parseInt(rgba[1], 16);			
						rgb['g']= parseInt(rgba[2], 16);			
						rgb['b']= parseInt(rgba[3], 16);
						
					}else{
						rgb = color;
					}
					
					return parseInt((rgb['r'] + rgb['g'] + rgb['b'])/3);
				}				
			}

		// Other data
			$.fn.evo_get_OD = function(){			
				var ev_cal = $(this);
				return ev_cal.find('.evo_cal_data').data('od');			
			}

	

		// get evo data for a given calendar
		$.fn.evo_getevodata = function(){

			var ev_cal = $(this);
			var evoData = {};
			
			ev_cal.find('.evo-data').each(function(){
				$.each(this.attributes, function(i, attrib){
					var name = attrib.name;
					if(attrib.name!='class' && attrib.name!='style' ){
						name__ = attrib.name.split('-');
						evoData[name__[1]] = attrib.value;	
					}
				});
			});	

			return evoData;
		}

		// check if mobile device v4.6
		$.fn.evo_is_mobile = function(){
			return ( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) )? true: false;		
		}

	// eventon loading functions
	// v 2.4.5 -- deprecating
		$.fn.evo_loader_animation = function(opt){
			var defaults = {
				direction:'start'
			}
			var OPT = $.extend({}, defaults, opt);

			if(OPT.direction == 'start'){
				$(this).find('#eventon_loadbar').slideDown();
			}else{
				$(this).find('#eventon_loadbar').slideUp();
			}
		}

	// DEPRECATED functions
		$.fn.evo_item_shortcodes = function(){			
			var OBJ = $(this);
			var shortcode_array ={};			
			OBJ.each(function(){	
				$.each(this.attributes, function(i, attrib){
					var name = attrib.name;
					if(attrib.name!='class' && attrib.name!='style' && attrib.value !=''){
						name__ = attrib.name.split('-');
						shortcode_array[name__[1]] = attrib.value;	
					}
				});
			});
			return shortcode_array;
		}
		$.fn.evo_shortcodes = function(){			
			var ev_cal = $(this);
			var shortcode_array ={};
					
			ev_cal.find('.cal_arguments').each(function(){
				$.each(this.attributes, function(i, attrib){
					var name = attrib.name;
					if(attrib.name!='class' && attrib.name!='style' && attrib.value !=''){
						name__ = attrib.name.split('-');
						shortcode_array[name__[1]] = attrib.value;	
					}
				});
			});	
			return shortcode_array;
		}
		

}(jQuery));