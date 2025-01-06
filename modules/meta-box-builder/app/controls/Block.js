import { Flex } from "@wordpress/components";
import { RawHTML, useEffect, useRef, useState } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import { UnControlled as CodeMirror } from 'react-codemirror2';
import { fetcher, getSettings } from "../functions";
import useObjectType from "../hooks/useObjectType";
import Checkbox from './Checkbox';
import DivRow from './DivRow';
import Icon from './Icon';
import Input from './Input';
import ReactSelect from './ReactSelect';
import Select from './Select';
import Textarea from './Textarea';
import { ensureArray } from '/functions';

const renderWithOptions = {
    callback: __( 'PHP callback function', 'meta-box-builder' ),
    template: __( 'Template file', 'meta-box-builder' ),
    code: __( 'Code', 'meta-box-builder' ),
};

if ( MbbApp.extensions.views ) {
    renderWithOptions[ 'view' ] = __( 'View', 'meta-box-builder' );
}

const getRenderViewId = ( renderView ) => {
    const view = Object.values( MbbApp.views )?.find( view => view.post_name === renderView );

    return view?.ID;
};

const Block = () => {
    const [ settings, setSettings ] = useState( getSettings() );
    const [ iconType, setIconType ] = useState( settings.icon_type || 'dashicons' );
    const [ renderWith, setRenderWith ] = useState( settings.render_with || 'callback' );
    const [ codeEditor, setCodeEditor ] = useState();

    const [ views, setViews ] = useState( MbbApp.views );
    const [ renderView, setRenderView ] = useState( settings.render_view );

    const addViewButtonRef = useRef();
    const editViewButtonRef = useRef();

    const codeRef = useRef();
    const objectType = useObjectType( state => state.type );

    useEffect( () => {
        jQuery( '.og-color-picker input[type="text"]' ).wpColorPicker();
    }, [ iconType ] );

    const updateIconType = e => setIconType( e.target.value );
    const updateRenderWith = e => setRenderWith( e.target.value );

    const [ blockPathError, setBlockPathError ] = useState( MbbApp.data?.block_path_error );
    const [ isNewer, setIsNewer ] = useState( false );

    const modalConfig = {
        hideElement: '#editor .interface-interface-skeleton__footer, .edit-post-fullscreen-mode-close',
        isBlockEditor: false,
        callback: ( $modal, $modalContent ) => {
            // Set the default type to block when adding a new view
            $modalContent.find( '#type' ).val( 'block' );
        },
        closeModalCallback: ( $modal, $input ) => {
            const postId = $modal.find( '#post_ID' ).val();
            const postName = $modal.find( '#post_name' ).val();
            const postTitle = $modal.find( '#title' ).val();

            setViews( {
                ...views,
                [ postId ]: { ID: postId, post_name: postName, post_title: postTitle }
            } );
            setRenderView( postName );
        },
    };
    /**
     * Get local path data, including whether the path is writable, block.json version.
     *
     * @param any _
     * @param string path
     */
    const getLocalPathData = async ( _, path ) => {
        const postName = document.getElementById( 'post_name' ).value;

        if ( !postName ) {
            return;
        }

        const { is_writable, is_newer } = await fetcher( 'local-path-data', {
            path,
            version: settings.block_json?.version || 0,
            postName
        } );

        const errorMessage = is_writable ? '' : __( 'The path is not writable.', 'meta-box-builder' );

        setIsNewer( is_newer );
        setBlockPathError( errorMessage );
    };

    const showEditViewModal = e => {
        const $this = jQuery( e );

        $this.attr( 'data-url', MbbApp.viewEditUrl + getRenderViewId( renderView ) + '&action=edit' );
        
        $this.rwmbModal( { ...modalConfig, isEdit: true } );
    };

    const showAddViewModal = e => {
        const $this = jQuery( e );

        $this.rwmbModal( { ...modalConfig, isEdit: true } );
    };

    const handleSelectView = e => {
        setRenderView( e.target.value );
    };

    useEffect( () => {
        if ( !settings.block_json?.path ) {
            return;
        }

        getLocalPathData( null, settings.block_json?.path );
    }, [] );

    useEffect( () => {
        if ( codeEditor ) {
            setTimeout( () => codeEditor.refresh(), 3000 );
        }
    }, [ codeEditor ] );

    useEffect( () => {
        showAddViewModal( addViewButtonRef?.current );
    }, [ addViewButtonRef.current, renderWith ] );

    useEffect( () => {
        showEditViewModal( editViewButtonRef?.current );
    }, [ editViewButtonRef.current, renderWith, renderView ] );

    return objectType === 'block' && <>
        <Input
            name="settings[description]"
            label={ __( 'Description', 'meta-box-builder' ) }
            componentId="settings-block-description"
            value={ settings.description }
            onChange={ e => setSettings( { ...settings, description: e.target.value } ) }
        />
        <Select
            name="settings[icon_type]"
            label={ __( 'Icon type', 'meta-box-builder' ) }
            componentId="settings-block-icon_type"
            options={ { dashicons: __( 'Dashicons', 'meta-box-builder' ), svg: __( 'Custom SVG', 'meta-box-builder' ) } }
            defaultValue={ iconType }
            onChange={ updateIconType }
        />
        {
            iconType === 'svg' &&
            <Textarea
                name="settings[icon_svg]"
                label={ __( 'SVG icon', 'meta-box-builder' ) }
                componentId="settings-block-icon_svg"
                placeholder={ __( 'Paste the SVG content here', 'meta-box-builder' ) }
                defaultValue={ settings.icon_svg }
            />
        }
        { iconType === 'dashicons' && <Icon label={ __( 'Icon', 'meta-box-builder' ) } name="settings[icon]" defaultValue={ settings.icon } /> }
        {
            iconType === 'dashicons' &&
            <Input
                name="settings[icon_foreground]"
                className="og-color-picker"
                componentId="settings-block-icon_foreground"
                label={ __( 'Icon color', 'meta-box-builder' ) }
                tooltip={ __( 'Leave empty to use default color', 'meta-box-builder' ) }
                defaultValue={ settings.icon_foreground }
            />
        }
        {
            iconType === 'dashicons' &&
            <Input
                name="settings[icon_background]"
                className="og-color-picker"
                componentId="settings-block-icon_background"
                label={ __( 'Icon background color', 'meta-box-builder' ) }
                tooltip={ __( 'Leave empty to use default color', 'meta-box-builder' ) }
                defaultValue={ settings.icon_background }
            />
        }
        <Select
            name="settings[category]"
            label={ __( 'Category', 'meta-box-builder' ) }
            componentId="settings-block-category"
            options={ MbbApp.blockCategories }
            defaultValue={ settings.category }
        />
        <Input
            name="settings[keywords]"
            label={ __( 'Keywords', 'meta-box-builder' ) }
            componentId="settings-block-keywords"
            tooltip={ __( 'Separate by commas', 'meta-box-builder' ) }
            defaultValue={ settings.keywords }
        />
        <Select
            name="settings[block_context]"
            label={ __( 'Block settings position', 'meta-box-builder' ) }
            componentId="settings-block-block_context"
            options={ {
                normal: __( 'In the content area', 'meta-box-builder' ),
                side: __( 'On the right sidebar', 'meta-box-builder' ),
            } }
            defaultValue={ settings.block_context || 'side' }
        />
        <ReactSelect
            name="settings[supports][align][]"
            label={ __( 'Alignment', 'meta-box-builder' ) }
            componentId="settings-block-supports-align"
            options={ {
                left: __( 'Left', 'meta-box-builder' ),
                right: __( 'Right', 'meta-box-builder' ),
                center: __( 'Center', 'meta-box-builder' ),
                wide: __( 'Wide', 'meta-box-builder' ),
                full: __( 'Full', 'meta-box-builder' ),
            } }
            defaultValue={ ensureArray( settings.supports?.align || [] ) }
        />

        <Checkbox
            name="settings[supports][customClassName]"
            label={ __( 'Custom CSS class name', 'meta-box-builder' ) }
            componentId="settings-block-supports-custom-class-name"
            defaultValue={ !!settings.supports?.customClassName }
        />

        <h3>{ __( 'Block Render Settings', 'meta-box-builder' ) }</h3>
        <Select
            name="settings[render_with]"
            label={ __( 'Render with', 'meta-box-builder' ) }
            componentId="settings-block-render_with"
            options={ renderWithOptions }
            defaultValue={ renderWith }
            onChange={ updateRenderWith }
        />
        {
            renderWith === 'callback' &&
            <Input
                name="settings[render_callback]"
                label={ __( 'Render callback', 'meta-box-builder' ) }
                componentId="settings-block-render_callback"
                placeholder={ __( 'Enter PHP function name', 'meta-box-builder' ) }
                defaultValue={ settings.render_callback }
            />
        }
        {
            renderWith === 'template' &&
            <Input
                name="settings[render_template]"
                label={ __( 'Render template', 'meta-box-builder' ) }
                componentId="settings-block-render_template"
                placeholder={ __( 'Enter absolute path to the template file', 'meta-box-builder' ) }
                defaultValue={ settings.render_template }
            />
        }
        {
            renderWith === 'code' &&
            <DivRow label={ __( 'Render code', 'meta-box-builder' ) }>
                <CodeMirror
                    options={ { mode: 'php' } }
                    value={ settings.render_code }
                    onChange={ ( editor, data, value ) => codeRef.current.value = value }
                    editorDidMount={ setCodeEditor }
                />
                <input type="hidden" name="settings[render_code]" ref={ codeRef } defaultValue={ settings.render_code } />
                <table className="og-block-description">
                    <tbody>
                        <tr>
                            <td><code>{ "{{ attribute }}" }</code></td>
                            <td><RawHTML>{ __( 'Block attribute. Replace <code>attribute</code> with <code>anchor</code>, <code>align</code> or <code>className</code>).', 'meta-box-builder' ) }</RawHTML></td>
                        </tr>
                        <tr>
                            <td><code>{ "{{ field_id }}" }</code></td>
                            <td><RawHTML>{ __( 'Field value. Replace <code>field_id</code> with a real field ID.', 'meta-box-builder' ) }</RawHTML></td>
                        </tr>
                        <tr>
                            <td><code>{ "{{ is_preview }}" }</code></td>
                            <td><RawHTML>{ __( 'Whether in preview mode.', 'meta-box-builder' ) }</RawHTML></td>
                        </tr>
                        <tr>
                            <td><code>{ "{{ post_id }}" }</code></td>
                            <td><RawHTML>{ __( 'Current post ID.', 'meta-box-builder' ) }</RawHTML></td>
                        </tr>
                        <tr>
                            <td><code>mb.function()</code></td>
                            <td><RawHTML>{ __( 'Run a PHP/WordPress function via <code>mb</code> namespace. Replace <code>function</code> with a valid PHP/WordPress function name.', 'meta-box-builder' ) }</RawHTML></td>
                        </tr>
                    </tbody>
                </table>
            </DivRow>
        }

        {
            renderWith === 'view' && MbbApp.extensions.views &&
            <DivRow label={ __( 'Select a view', 'meta-box-builder' ) } className="og-field--block-view">
                <select
                    name="settings[render_view]"
                    componentId="settings-block-render_view"
                    value={ renderView }
                    onChange={ handleSelectView }
                >
                    <option value="">{ __( 'Select a view', 'meta-box-builder' ) }</option>
                    { Object.entries( views ).map( ( [ id, view ] ) => (
                        <option data-id={ id } value={ view.post_name }>{ view.post_title }</option>
                    ) ) }
                </select>

                <Flex justify="left">
                    <a
                        href="#"
                        ref={ addViewButtonRef }
                        role="button"
                        data-url={ MbbApp.viewAddUrl }
                    >{ __( '+ Add View', 'meta-box-builder' ) }</a>

                    { renderView &&
                        <a
                            href="#"
                            ref={ editViewButtonRef }
                            role="button"
                        >{ __( 'Edit View', 'meta-box-builder' ) }</a>
                    }
                </Flex>
            </DivRow>
        }

        <Input
            name="settings[enqueue_style]"
            label={ __( 'Custom CSS', 'meta-box-builder' ) }
            componentId="settings-block-enqueue_style"
            placeholder={ __( 'Enter URL to the custom CSS file', 'meta-box-builder' ) }
            defaultValue={ settings.enqueue_style }
        />
        <Input
            name="settings[enqueue_script]"
            label={ __( 'Custom JavaScript', 'meta-box-builder' ) }
            componentId="settings-block-enqueue_script"
            placeholder={ __( 'Enter URL to the custom JavaScript file', 'meta-box-builder' ) }
            defaultValue={ settings.enqueue_script }
        />
        <Input
            name="settings[enqueue_assets]"
            label={ __( 'Custom assets callback', 'meta-box-builder' ) }
            componentId="settings-block-enqueue_assets"
            placeholder={ __( 'Enter PHP callback function name', 'meta-box-builder' ) }
            defaultValue={ settings.enqueue_assets }
        />

        <h3>{ __( 'Block JSON Settings', 'meta-box-builder' ) }</h3>
        <Checkbox
            name="settings[block_json][enable]"
            label={ __( 'Generate block.json', 'meta-box-builder' ) }
            componentId="settings-block_json_enable"
            defaultValue={ !!settings.block_json?.enable }
        />

        <Input
            name="settings[block_json][path]"
            label={ __( 'Block folder', 'meta-box-builder' ) }
            componentId="settings-block-path"
            description={ __( 'Enter absolute path to the folder containing the <code>block.json</code> and block asset files. <b>Do not include the block name (e.g. field group ID)</b>. The full path for the block files will be like <code>path/to/folder/block-name/block.json</code>.', 'meta-box-builder' ) }
            defaultValue={ settings.block_json?.path }
            error={ blockPathError }
            updateFieldData={ getLocalPathData }
            dependency="block_json_enable:true"
        />

        <input type="hidden" name="settings[block_json][version]" value={ settings.block_json?.version } />

        { isNewer &&
            <DivRow label={ __( 'Synchronize block.json', 'meta-box-builder' ) }>
                <Flex direction="column">
                    <div dangerouslySetInnerHTML={ {
                        __html: __( 'We detected a newer version of <code>block.json</code> from the current folder, do you want to override settings from this path?', 'meta-box-builder' )
                    } }></div>

                    <div>
                        <input
                            name="override_block_json"
                            value={ __( 'Override Block JSON', 'meta-box-builder' ) }
                            type="submit"
                            class="button secondary"
                            onClick={ ( e ) => {
                                if ( !confirm( __( 'Are you sure you want to override the block.json settings?', 'meta-box-builder' ) ) ) {
                                    e.preventDefault();
                                }
                            } }
                        />
                    </div>
                </Flex>
            </DivRow>
        }

        <DivRow label={ __( 'Supported variables', 'meta-box-builder' ) } >
            <table className="og-block-description">
                <tbody>
                    <tr>
                        <td><code>{ "{{ site.path }}" }</code></td>
                        <td>{ __( 'Site path', 'meta-box-builder' ) }</td>
                    </tr>
                    <tr>
                        <td><code>{ "{{ site.url }}" }</code></td>
                        <td>{ __( 'Site URL', 'meta-box-builder' ) }</td>
                    </tr>
                    <tr>
                        <td><code>{ "{{ theme.path }}" }</code></td>
                        <td>{ __( 'Path to the current [child] theme directory', 'meta-box-builder' ) }</td>
                    </tr>
                    <tr>
                        <td><code>{ "{{ theme.url }}" }</code></td>
                        <td>{ __( 'URL to the current [child] theme directory', 'meta-box-builder' ) }</td>
                    </tr>
                </tbody>
            </table>
        </DivRow>
    </>;
};

export default Block;