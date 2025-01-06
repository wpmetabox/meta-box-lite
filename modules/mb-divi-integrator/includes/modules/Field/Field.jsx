import React, { Component } from 'react';

class Field extends Component {
  static slug = 'mbdi_field';

  componentDidMount() {
    fetch(window.etCore.ajaxurl + '?action=mb_divi_integrator_get_fields', {
      method: 'GET',
    })
      .then((response) => {
        return response.json();
      })
      .then((data) => {
        if (data) {
          this.setState({
            fields: data.data,
          });
            this.updateComputedFields();
        }
      });
  }

  componentDidUpdate(prevProps, prevState) {
    if (!this.state) {
      return;
    }

    if (this.props.metabox_field_id !== prevProps.metabox_field_id) {
      this.updateComputedFields();
    }
  }

  updateComputedFields() {
    const fields = this.state.fields;

    if (!fields) {
      return;
    }

    // Update computed fields
    const selectedField =
      fields.field_options[this.props.metabox_field_id] ||
      fields.field_options[0];
      
    this.setState({
      selectedField,
    });
  }

  render() {
    return (
      <div className="mbdi-field">
        {!this.state && <div className="mbdi-field__title">Meta Box Field</div>}
        {this.state && (
          <div className="mbdi-field__title">
            Meta Box Field: {this.state.selectedField}
          </div>
        )}
      </div>
    );
  }
}

export default Field;
