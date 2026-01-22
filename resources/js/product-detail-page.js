document.addEventListener('alpine:init', () => {
    Alpine.data('productDetailPage', (
        firstImage,
        firstColorId,
        colorAttributeValues,
        panelTypeAttributeValues,
        basePrice,
        firstPanelId
    ) => ({
        mainImage: firstImage,
        selectedColor: firstColorId,
        colorAttributeValues: colorAttributeValues,
        panelTypeAttributeValues: panelTypeAttributeValues,
        basePrice: basePrice,
        currentPrice: basePrice,
        selectedPanel: firstPanelId,

        formatCurrency(value) {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'EUR'
            }).format(value);
        },

        setNewPrice(foundAttribute,newPrice){
            if (foundAttribute.attribute_value_method === 'attribute.value.method.fixed') {
                newPrice += parseFloat(foundAttribute.attribute_value);
            } else if (foundAttribute.attribute_value_method === 'attribute.value.method.percent') {
                newPrice *= (1 + parseFloat(foundAttribute.attribute_value) / 100);
            }
            return newPrice
        },

        init() {
            this.calculatePrice();
            this.$watch('selectedColor', (newColor) => {
                this.calculatePrice();
                if (newColor === null) {
                    this.mainImage = firstImage;
                    return;
                }
                const foundAttribute = this.colorAttributeValues.find(attr => String(attr.attribute_option_id) === String(newColor));
                if (foundAttribute && foundAttribute.media.length > 0) {
                    this.mainImage = foundAttribute.media[0].original_url;
                }
            });
            this.$watch('selectedPanel', () => {
                this.calculatePrice();
            });
        },

        calculatePrice() {
            let newPrice = this.basePrice;
            if (this.selectedColor !== null) {
                const foundAttribute = this.colorAttributeValues.find(attr => String(attr.attribute_option_id) === String(this.selectedColor));
                if (foundAttribute) {
                    newPrice = this.setNewPrice(foundAttribute,newPrice);
                }
            }
            if (this.selectedPanel !== null) {
                const foundAttribute = this.panelTypeAttributeValues.find(attr => String(attr.attribute_option_id) === String(this.selectedPanel));
                if (foundAttribute) {
                    newPrice = this.setNewPrice(foundAttribute,newPrice);
                }
            }
            this.currentPrice = newPrice;
        }
    }));
});
