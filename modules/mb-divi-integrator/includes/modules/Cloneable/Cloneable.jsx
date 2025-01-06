// External Dependencies
import React, { Component } from 'react';

class Cloneable extends Component {
  static slug = 'mbdi_cloneable';

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

    if (
      this.props.layout !== prevProps.layout ||
      this.props.field !== prevProps.field
    ) {
      this.updateComputedFields();
    }
  }

  updateComputedFields() {
    const fields = this.state.fields;

    if (!fields) {
      return;
    }

    // Update computed fields
    const selectedLayout =
      fields.layout_options[this.props.layout] || fields.layout_options[0];
    const selectedField =
      fields.cloneable_field_options[this.props.field] ||
      fields.cloneable_field_options[0];

    this.setState({
      selectedLayout,
      selectedField,
    });
  }

  render() {
    return (
      <div className="mbdi-field">
        {!this.state && (
          <div className="mbdi-field__title">Meta Box Cloneable</div>
        )}

        {this.state && this.state.selectedLayout && this.state.selectedField && (
          <div className="mbdi-field__title">
            Meta Box Cloneable: {this.state.selectedLayout} <em>on</em>{' '}
            {this.state.selectedField}
          </div>
        )}
      </div>
    );
  }
}

export default Cloneable;
