@{
    IncludeRules = @(
        'PSAvoidAssignmentToAutomaticVariable'
        'PSAvoidDefaultValueForMandatoryParameter'
        'PSAvoidDefaultValueSwitchParameter'
        'PSAvoidGlobalAliases'
        'PSAvoidGlobalFunctions'
        'PSAvoidGlobalVars'
        'PSAvoidInvokingEmptyMembers'
        'PSAvoidNullOrEmptyHelpMessageAttribute'
        'PSAvoidOverwritingBuiltInCmdlets'
        'PSAvoidShouldContinueWithoutForce'
        'PSAvoidTrailingWhitespace'
        'PSAvoidUsingCmdletAliases'
        'PSAvoidUsingComputerNameHardcoded'
        'PSAvoidUsingConvertToSecureStringWithPlainText'
        'PSAvoidUsingDeprecatedManifestFields'
        'PSAvoidUsingEmptyCatchBlock'
        'PSAvoidUsingInvokeExpression'
        'PSAvoidUsingPlainTextForPassword'
        'PSAvoidUsingPositionalParameters'
        'PSAvoidUsingUsernameAndPasswordParams'
        'PSAvoidUsingWMICmdlet'
        'PSMisleadingBacktick'
        'PSMissingModuleManifestField'
        'PSPlaceCloseBrace'
        'PSPlaceOpenBrace'
        'PSPossibleIncorrectComparisonWithNull'
        'PSPossibleIncorrectUsageOfAssignmentOperator'
        'PSPossibleIncorrectUsageOfRedirectionOperator'
        'PSProvideCommentHelp'
        'PSReservedCmdletChar'
        'PSReservedParams'
        'PSReviewUnusedParameter'
        'PSShouldProcess'
        'PSUseApprovedVerbs'
        'PSUseBOMForUnicodeEncodedFile'
        'PSUseCmdletCorrectly'
        'PSUseCompatibleCmdlets'
        'PSUseConsistentIndentation'
        'PSUseConsistentWhitespace'
        'PSUseCorrectCasing'
        'PSUseDeclaredVarsMoreThanAssignments'
        'PSUseLiteralInitializerForHashtable'
        'PSUseOutputTypeCorrectly'
        'PSUseProcessBlockForPipelineCommand'
        'PSUsePSCredentialType'
        'PSUseShouldProcessForStateChangingFunctions'
        'PSUseSingularNouns'
        'PSUseSupportsShouldProcess'
        'PSUseToExportFieldsInManifest'
        'PSUseUsingScopeModifierInNewRunspaces'
        'PSUseUTF8EncodingForHelpFile'
    )
    Rules = @{
        PSPlaceOpenBrace = @{
            OnSameLine = $true
            NewLineAfter = $true
            IgnoreOneLineBlock = $true
        }
        PSPlaceCloseBrace = @{
            Enable = $true
            NewLineAfter = $true
            IgnoreOneLineBlock = $true
            NoEmptyLineBefore = $false
        }

        PSUseConsistentIndentation = @{
            Enable = $true
            Kind = 'space'
            PipelineIndentation = 'IncreaseIndentationForFirstPipeline'
            IndentationSize = 4
        }

        PSUseConsistentWhitespace = @{
            Enable = $true
            CheckInnerBrace = $true
            CheckOpenBrace = $true
            CheckOpenParen = $true
            CheckOperator = $true
            CheckPipe = $true
            CheckPipeForRedundantWhitespace = $false
            CheckSeparator = $true
            CheckParameter = $false
        }
        PSUseCorrectCasing = @{
            Enable = $true
        }
    }
}
