import { useState } from "@wordpress/element";
import { __ } from "@wordpress/i18n";
import { getSettings } from "../functions";
import useObjectType from "../hooks/useObjectType";
import usePostTypes from "../hooks/usePostTypes";
import Checkbox from './Checkbox';
import DivRow from './DivRow';
import ReactSelect from './ReactSelect';
import Select from './Select';
import { ensureArray } from '/functions';

const Location = () => {
	const settings = getSettings();
	const [ settingsPages, setSettingsPages ] = useState( ensureArray( settings.settings_pages || [] ) );

	const selectedSettingsPage = MbbApp.settingsPages.find( p => settingsPages.includes( p.id ) );
	const tabs = selectedSettingsPage ? selectedSettingsPage.tabs : [];

	const objectType = useObjectType( state => state.type );
	const setObjectType = useObjectType( state => state.update );
	const postTypes = usePostTypes( state => state.types );
	const setPostTypes = usePostTypes( state => state.update );

	return (
		<>
			<DivRow label={ __( 'Location', 'meta-box-builder' ) } htmlFor="settings-object_type" className="og-location" tooltip={ __( 'Select where to display the field group', 'meta-box-builder' ) }>
				<select id="settings-object_type" name="settings[object_type]" defaultValue={ objectType } onChange={ e => setObjectType( e.target.value ) }>
					<option value="post">{ __( 'Post type', 'meta-box-builder' ) }</option>
					{ MbbApp.extensions.termMeta && <option value="term">{ __( 'Taxonomy', 'meta-box-builder' ) }</option> }
					{ MbbApp.extensions.userMeta && <option value="user">{ __( 'User', 'meta-box-builder' ) }</option> }
					{ MbbApp.extensions.commentMeta && <option value="comment">{ __( 'Comment', 'meta-box-builder' ) }</option> }
					{ MbbApp.extensions.settingsPage && <option value="setting">{ __( 'Settings page', 'meta-box-builder' ) }</option> }
					{ MbbApp.extensions.blocks && <option value="block">{ __( 'Block', 'meta-box-builder' ) }</option> }
				</select>
				{
					'post' === objectType &&
					<ReactSelect
						wrapper={ false }
						name="settings[post_types][]"
						options={ MbbApp.postTypes.map( item => ( { value: item.slug, label: `${ item.name } (${ item.slug })` } ) ) }
						defaultValue={ postTypes }
						onChange={ items => setPostTypes( items ? items.map( item => item.value ) : [] ) }
					/>
				}
				{
					'term' === objectType &&
					<ReactSelect
						wrapper={ false }
						name="settings[taxonomies][]"
						options={ MbbApp.taxonomies.map( item => ( { value: item.slug, label: `${ item.name } (${ item.slug })` } ) ) }
						defaultValue={ ensureArray( settings.taxonomies || [] ) }
					/>
				}
				{
					'setting' === objectType &&
					<ReactSelect
						wrapper={ false }
						name="settings[settings_pages][]"
						options={ MbbApp.settingsPages.map( item => ( { value: item.id, label: `${ item.title } (${ item.id })` } ) ) }
						defaultValue={ ensureArray( settings.settings_pages || [] ) }
						onChange={ items => setSettingsPages( items ? items.map( item => item.value ) : [] ) }
					/>
				}
			</DivRow>
			{
				'post' === objectType && postTypes.includes( 'attachment' ) &&
				<Checkbox
					label={ __( 'Show in media modal', 'meta-box-builder' ) }
					name="settings[media_modal]"
					defaultValue={ !!settings.media_modal }
					componentId="settings-media_modal"
				/>
			}
			{
				'setting' === objectType && Object.keys( tabs ).length > 0 &&
				<Select
					label={ __( 'Tab', 'meta-box-builder' ) }
					tooltip={ __( 'Select a tab in the settings page that the field group belongs to', 'meta-box-builder' ) }
					name="settings[tab]"
					options={ tabs }
					defaultValue={ settings.tab }
					componentId="settings-tab"
				/>
			}
		</>
	);
};

export default Location;