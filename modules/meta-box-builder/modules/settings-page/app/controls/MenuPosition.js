import Select from '/controls/Select';

const MenuPosition = props => <Select { ...props } options={ MbbApp.menu_positions } />;
export default MenuPosition;