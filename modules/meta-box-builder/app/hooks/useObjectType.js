import { create } from 'zustand';
import { getSettings } from '../functions';

const settings = getSettings();

const useObjectType = create( set => ( {
	type: settings.object_type || 'post',
	update: type => set( state => ( { type } ) )
} ) );

export default useObjectType;