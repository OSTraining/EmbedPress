/**
 * Internal dependencies
 */

import Iframe from '../common/Iframe';
import ControlHeader from '../common/control-heading';
import Logo from '../common/Logo';
import EmbedLoading from '../common/embed-loading';



/**
 * WordPress dependencies
 */

const { __ } = wp.i18n;
const { getBlobByURL, isBlobURL, revokeBlobURL } = wp.blob;
const { BlockIcon, MediaPlaceholder, InspectorControls } = wp.blockEditor;
const { Component, Fragment, useEffect } = wp.element;
const { RangeControl, PanelBody, ExternalLink, ToggleControl, SelectControl, RadioControl, ColorPalette } = wp.components;

import {
	__experimentalToggleGroupControl as ToggleGroupControl,
	__experimentalToggleGroupControlOption as ToggleGroupControlOption,
} from '@wordpress/components';

import { PdfIcon } from '../common/icons'


const ALLOWED_MEDIA_TYPES = [
	'application/pdf',
];

class EmbedPressPDFEdit extends Component {
	constructor() {
		super(...arguments);
		this.onSelectFile = this.onSelectFile.bind(this);

		this.onUploadError = this.onUploadError.bind(this);
		this.onLoad = this.onLoad.bind(this);
		this.hideOverlay = this.hideOverlay.bind(this);
		this.isPro = this.isPro.bind(this);
		this.addProAlert = this.addProAlert.bind(this);

		this.state = {
			hasError: false,
			fetching: false,
			interactive: false,
			loadPdf: true,
		};
	}


	componentDidMount() {

		const {
			attributes,
			mediaUpload,
			noticeOperations
		} = this.props;
		const { href } = attributes;

		// Upload a file drag-and-dropped into the editor
		if (isBlobURL(href)) {
			const file = getBlobByURL(href);

			mediaUpload({
				filesList: [file],
				onFileChange: ([media]) => this.onSelectFile(media),
				onError: (message) => {
					this.setState({ hasError: true });
					noticeOperations.createErrorNotice(message);
				},
			});

			revokeBlobURL(href);
		}

		if (this.props.attributes.href && this.props.attributes.mime === 'application/pdf' && this.state.loadPdf) {
			this.setState({ loadPdf: false });
		}

	}

	componentDidUpdate(prevProps) {

		// Reset copy confirmation state when block is deselected
		if (prevProps.isSelected && !this.props.isSelected) {
			this.setState({ showCopyConfirmation: false });
		}

	}

	static getDerivedStateFromProps(nextProps, state) {
		if (!nextProps.isSelected && state.interactive) {
			return { interactive: false };
		}

		return null;
	}

	hideOverlay() {
		this.setState({ interactive: true });
	}

	onLoad() {
		this.setState({
			fetching: false
		})
	}

	onSelectFile(media) {
		if (media && media.url) {
			this.setState({ hasError: false });
			this.props.setAttributes({
				href: media.url,
				fileName: media.title,
				id: 'embedpress-pdf-' + Date.now(),
				mime: media.mime,
			});

			if (embedpressObj.branding !== undefined && embedpressObj.branding.powered_by !== undefined) {
				this.props.setAttributes({
					powered_by: embedpressObj.branding.powered_by
				});
			}

			if (media.mime === 'application/pdf') {
				this.setState({ loadPdf: false });
			}
		}

	}

	onUploadError(message) {
		const { noticeOperations } = this.props;
		noticeOperations.removeAllNotices();
		noticeOperations.createErrorNotice(message);
	}

	addProAlert(e, isProPluginActive) {
		if (!isProPluginActive) {
			document.querySelector('.pro__alert__wrap').style.display = 'block';
		}
	}

	removeAlert() {
		if (document.querySelector('.pro__alert__wrap')) {
			document.querySelector('.pro__alert__wrap .pro__alert__card .button').addEventListener('click', (e) => {
				document.querySelector('.pro__alert__wrap').style.display = 'none';
			});
		}
	}


	isPro(display) {
		const alertPro = `
		<div class="pro__alert__wrap" style="display: none;">
			<div class="pro__alert__card">
				<img src="../wp-content/plugins/embedpress/EmbedPress/Ends/Back/Settings/assets/img/alert.svg" alt=""/>
					<h2>Opps...</h2>
					<p>You need to upgrade to the <a href="https://wpdeveloper.com/in/upgrade-embedpress" target="_blank">Premium</a> Version to use this feature</p>
					<a href="#" class="button radius-10">Close</a>
			</div>
		</div>
		`;

		const dom = document.createElement('div');
		dom.innerHTML = alertPro;

		return dom;

	}


	render() {

		const { attributes, noticeUI, setAttributes, clientId } = this.props;

		const { href, mime, id, unitoption, width, height, powered_by, themeMode, customColor, presentation, position, download, add_text, draw, open, toolbar, copy_text, toolbar_position, doc_details, doc_rotation } = attributes;


		const { hasError, interactive, fetching, loadPdf } = this.state;
		const min = 1;
		const max = 1000;

		const colors = [
			{ name: '', color: '#823535' },
			{ name: '', color: '#008000' },
			{ name: '', color: '#403A81' },
			{ name: '', color: '#333333' },
			{ name: '', color: '#000264' },
		]; 

		let widthMin = 0;
		let widthMax = 100;

		if (unitoption == 'px') {
			widthMax = 1500;
		}

		const docLink = 'https://embedpress.com/docs/embed-document/';
		const isProPluginActive = embedpressObj.is_pro_plugin_active;

		if (!isProPluginActive) {
			setAttributes({ download: true });
			setAttributes({ copy_text: true });
			setAttributes({ draw: false });
		}

		if (!document.querySelector('.pro__alert__wrap')) {
			document.querySelector('body').append(this.isPro('none'));
			this.removeAlert();
		}

		function getParamData(href) {
			let pdf_params = '';
			let colorsObj = {};

			//Generate PDF params
			if(themeMode === 'custom') {
				colorsObj = {
					customColor: (customColor && (customColor !== 'default')) ? customColor : '#403A81',
				}
			}
			let _pdf_params = {
				themeMode: themeMode ? themeMode : 'default',
				...colorsObj,
				presentation: presentation ? presentation : false,
				position: position ? position : 'top',
				download: download ? download : false,
				toolbar: toolbar ? toolbar : false,
				copy_text: copy_text ? copy_text : false,
				add_text: add_text ? add_text : false,
				draw: draw ? draw : false,
				toolbar_position: toolbar_position ? toolbar_position : 'top',
				doc_details: doc_details ? doc_details : false,
				doc_rotation: doc_rotation ? doc_rotation : false,
			};

			pdf_params = new URLSearchParams(_pdf_params).toString();

			let __url = href.split('#');
			__url = encodeURIComponent(__url[0]);

			return `${__url}#${pdf_params}`;
		}

		if (!href || hasError) {
			return (
				<div className={"embedpress-document-editmode"} >
					<MediaPlaceholder
						icon={<BlockIcon icon={PdfIcon} />}
						labels={{
							title: __('EmbedPress PDF'),
							instructions: __(
								'Upload a PDF file or pick one from your media library for embed.'
							),
						}}
						onSelect={this.onSelectFile}
						notices={noticeUI}
						allowedTypes={ALLOWED_MEDIA_TYPES}
						onError={this.onUploadError}

					>

						<div style={{ width: '100%' }} className="components-placeholder__learn-more embedpress-doc-link">
							<ExternalLink href={docLink}>Learn more about Embedded document </ExternalLink>
						</div>
					</MediaPlaceholder>

				</div>

			);
		} else {
			const url = '//view.officeapps.live.com/op/embed.aspx?src=' + getParamData(href);
			const pdf_viewer_src = embedpressObj.pdf_renderer + '?file=' + getParamData(href);

			// this.iframeManupulate(`.${id}`, themeMode, presentation, position, download, open, toolbar, copy_text, toolbar_position, doc_details, doc_rotation);

			return (
				<Fragment>

					{(fetching && mime !== 'application/pdf') ? <EmbedLoading /> : null}
					<div className={'embedpress-document-embed ep-doc-' + id} style={{ width: width + unitoption, maxWidth: '100%' }} id={`ep-doc-${this.props.clientId}`}>
						{mime === 'application/pdf' && (
							<iframe title="" powered_by={powered_by} style={{ height: height, width: '100%' }} className={'embedpress-embed-document-pdf' + ' ' + id} data-emid={id} data-emsrc={href} src={pdf_viewer_src}></iframe>

						)}

						{mime !== 'application/pdf' && (
							<Iframe title="" onMouseUponMouseUp={this.hideOverlay} style={{ height: height, width: width, display: fetching || !loadPdf ? 'none' : '' }} onLoad={this.onLoad} src={url} />
						)}
						{!interactive && (
							<div
								className="block-library-embed__interactive-overlay"
								onMouseUp={this.hideOverlay}
							/>
						)}
						{powered_by && (
							<p className="embedpress-el-powered">Powered By EmbedPress</p>
						)}

						{!fetching && <Logo id={id} />}

					</div>

					<InspectorControls key="inspector">
						<PanelBody
							title={__('Embed Size(px)', 'embedpress')}
						>
							<div className={'ep-pdf-width-contol'}>
								<ControlHeader classname={'ep-control-header'} headerText={'WIDTH'} />
								<RadioControl
									selected={unitoption}
									options={[
										{ label: '%', value: '%' },
										{ label: 'PX', value: 'px' },
									]}
									onChange={(unitoption) =>
										setAttributes({ unitoption })
									}
									className={'ep-unit-choice-option'}
								/>

								<RangeControl
									value={width}
									onChange={(width) =>
										setAttributes({ width })
									}
									max={widthMax}
									min={widthMin}
								/>

							</div>

							<RangeControl
								label={__(
									'Height',
									'embedpress'
								)}
								value={height}
								onChange={(height) =>
									setAttributes({ height })
								}
								max={max}
								min={min}
							/>
						</PanelBody>

						<PanelBody
							title={__('PDF Control Settings', 'embedpress')}
							initialOpen={false}
						>

							<SelectControl
								label="Theme"
								value={themeMode}
								options={[
									{ label: 'System Default', value: 'default' },
									{ label: 'Dark', value: 'dark' },
									{ label: 'Light', value: 'light' },
									{ label: 'Custom', value: 'custom' },
								]}
								onChange={(themeMode) =>
									setAttributes({ themeMode })
								}
								__nextHasNoMarginBottom
							/>

							{
								(themeMode === 'custom') && (
									<div>
										<ControlHeader headerText={'Color'} />
										<ColorPalette
											label={__("Color")}
											colors={colors}
											value={customColor}
											onChange={(customColor) => setAttributes({ customColor })}
										/>
									</div>
								)
							}

							<div className={isProPluginActive ? "pro-control-active" : "pro-control"} onClick={(e) => { this.addProAlert(e, isProPluginActive) }}>
								<ToggleControl
									label={__('Toolbar', 'embedpress')}
									description={__('Show or Hide toolbar. Note: If you disable toolbar access then every toolbar options will be disabled', 'embedpress')}
									onChange={(toolbar) =>
										setAttributes({ toolbar })
									}
									checked={toolbar}
									style={{ marginTop: '30px' }}
								/>
								{
									(!isProPluginActive) && (
										<span className='isPro'>{__('pro', 'embedpress')}</span>
									)
								}
							</div>


							{
								toolbar && (
									<Fragment>
										<ToggleGroupControl label="Toolbar Position" value={position} onChange={(position) => setAttributes({ position })}>
											<ToggleGroupControlOption value="top" label="Top" />
											<ToggleGroupControlOption value="bottom" label="Bottom" />
										</ToggleGroupControl>


										<ToggleControl
											label={__('Presentation Mode', 'embedpress')}
											onChange={(presentation) =>
												setAttributes({ presentation })
											}
											checked={presentation}
										/>

										<div className={isProPluginActive ? "pro-control-active" : "pro-control"} onClick={(e) => { this.addProAlert(e, isProPluginActive) }}>
											<ToggleControl
												label={__('Print/Download', 'embedpress')}
												onChange={(download) =>
													setAttributes({ download })
												}
												checked={download}
											/>
											{
												(!isProPluginActive) && (
													<span className='isPro'>{__('pro', 'embedpress')}</span>
												)
											}
										</div>

										<ToggleControl
											label={__('Add Text', 'embedpress')}
											onChange={(add_text) =>
												setAttributes({ add_text })
											}
											checked={add_text}
										/>

										<div className={isProPluginActive ? "pro-control-active" : "pro-control"} onClick={(e) => { this.addProAlert(e, isProPluginActive) }}>
											<ToggleControl
												label={__('Draw', 'embedpress')}
												onChange={(draw) =>
													setAttributes({ draw })
												}
												checked={draw}
											/>
											{
												(!isProPluginActive) && (
													<span className='isPro'>{__('pro', 'embedpress')}</span>
												)
											}
										</div>

										<div className={isProPluginActive ? "pro-control-active" : "pro-control"} onClick={(e) => { this.addProAlert(e, isProPluginActive) }}>
											<ToggleControl
												label={__('Copy Text', 'embedpress')}
												onChange={(copy_text) =>
													setAttributes({ copy_text })
												}
												checked={copy_text}
												className={'disabled'}
											/>
											{
												(!isProPluginActive) && (
													<span className='isPro'>{__('pro', 'embedpress')}</span>
												)
											}
										</div>
										<ToggleControl
											label={__('Rotation', 'embedpress')}
											onChange={(doc_rotation) =>
												setAttributes({ doc_rotation })
											}
											checked={doc_rotation}
										/>
										<ToggleControl
											label={__('Properties', 'embedpress')}
											onChange={(doc_details) =>
												setAttributes({ doc_details })
											}
											checked={doc_details}
										/>
										<ToggleControl
											label={__('Powered By', 'embedpress')}
											onChange={(powered_by) =>
												setAttributes({ powered_by })
											}
											checked={powered_by}
										/>


									</Fragment>
								)
							}
						</PanelBody>
					</InspectorControls>

					<style style={{ display: "none" }}>
						{
							`
							#block-${clientId} {
								width:-webkit-fill-available;
							}
							.embedpress-el-powered{
								max-width: ${width}
							}

							.alignright .embedpress-document-embed{
								float: right!important;
							}
							.alignleft .embedpress-document-embed{
								float: left;
							}

							`
						}
					</style>
				</Fragment >

			);
		}

	}

}

export default EmbedPressPDFEdit;
