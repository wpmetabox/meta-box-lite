import Select from '/controls/Select';

const MenuParent = props => <Select { ...props } options={ MbbApp.menu_parents } />;
export default MenuParent;