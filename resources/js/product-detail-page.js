document.addEventListener('alpine:init', () => {
    Alpine.data('productDetailPage', (
        firstImage,
        basePrice,
        attributes,
        colorAttributeValuesForGallery
    ) => ({
        mainImage: firstImage,
        basePrice: basePrice,
        currentPrice: basePrice,
        attributes: attributes,
        colorAttributeValuesForGallery: colorAttributeValuesForGallery,
        selectedAttributes: {},

        formatCurrency(value) {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'EUR'
            }).format(value);
        },

        setNewPrice(foundAttribute, newPrice) {
            if (foundAttribute.attribute_value_method === 'attribute.value.method.fixed') {
                newPrice += parseFloat(foundAttribute.attribute_value);
            } else if (foundAttribute.attribute_value_method === 'attribute.value.method.percent') {
                newPrice *= (1 + parseFloat(foundAttribute.attribute_value) / 100);
            }
            return newPrice;
        },

        init() {
            // Initialize selectedAttributes from the 'initial' value of each attribute
            this.attributes.forEach(attr => {
                this.selectedAttributes[attr.name] = attr.initial;
            });

            this.calculatePrice();

            // Watch the whole object for changes
            this.$watch('selectedAttributes', () => {
                this.calculatePrice();

                // Specific logic for color changing the main image
                const selectedColorId = this.selectedAttributes['color'];
                if (selectedColorId === undefined) return;

                if (selectedColorId === null) {
                    this.mainImage = firstImage;
                    return;
                }
                const foundAttribute = this.colorAttributeValuesForGallery.find(attr => String(attr.attribute_option_id) === String(selectedColorId));
                if (foundAttribute && foundAttribute.media.length > 0) {
                    this.mainImage = foundAttribute.media[0].original_url;
                }
            }, { deep: true });
        },

        calculatePrice() {
            let newPrice = parseFloat(this.basePrice);
            this.attributes.forEach(attribute => {
                const selectedOptionId = this.selectedAttributes[attribute.name];
                if (selectedOptionId !== null) {
                    const foundValue = attribute.values.find(val => String(val.attribute_option_id) === String(selectedOptionId));
                    if (foundValue) {
                        newPrice = this.setNewPrice(foundValue, newPrice);
                    }
                }
            });
            this.currentPrice = newPrice;
        }
    }));
});
